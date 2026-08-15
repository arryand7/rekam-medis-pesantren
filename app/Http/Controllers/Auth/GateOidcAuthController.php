<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApplicationIdentityService;
use App\Services\AuditLogService;
use App\Services\Gate\GateAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GateOidcAuthController extends Controller
{
    public function __construct(
        protected GateAuthenticationService $authService,
        protected ApplicationIdentityService $identityService
    ) {}

    /**
     * Show login page or redirect to Gate authorization URL.
     */
    public function login(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // If user explicitly clicks/requests Gate SSO redirect
        if ($request->has('redirect') || $request->has('sso')) {
            $redirectUrl = $this->authService->initiateLogin($request);

            return redirect()->away($redirectUrl);
        }

        return view('pages.auth.login', ['identity' => $this->identityService->get()]);
    }

    /**
     * Authenticate user with username/email and password.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Email atau username / NIS / NIP wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $throttleKey = Str::lower($credentials['login']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik."]);
        }

        $loginInput = trim($credentials['login']);
        $password = $credentials['password'];
        $remember = $request->boolean('remember');

        $user = User::where('email', $loginInput)
            ->orWhere('name', $loginInput)
            ->orWhereHas('person', function ($q) use ($loginInput) {
                $q->where('nis_nip', $loginInput)
                    ->orWhere('email', $loginInput);
            })
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($throttleKey);

            return back()->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => 'Email/username atau kata sandi yang Anda masukkan tidak sesuai.']);
        }

        if (! $user->is_active) {
            return back()->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator sistem.']);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->update([
            'last_login_at' => now(),
        ]);

        AuditLogService::log(
            action: 'local_login',
            subjectType: 'User',
            subjectId: $user->id,
            before: null,
            after: ['login_method' => 'credentials', 'ip' => $request->ip()],
            reason: 'Pengguna berhasil masuk langsung via email/username dan kata sandi'
        );

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Selamat datang kembali, '.$user->name.'!');
    }

    /**
     * Handle OAuth / OIDC callback from Gate.
     */
    public function callback(Request $request): RedirectResponse
    {
        $result = $this->authService->handleCallback($request);

        if ($result['status'] === 'success') {
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang! Anda berhasil masuk melalui Gate SSO.');
        }

        if ($result['status'] === 'entitlement_denied') {
            $entitlementStatus = $result['entitlement'] ? $result['entitlement']->status : 'not_assigned';

            return redirect()->route('auth.gate.access_denied')
                ->with('entitlement_status', $entitlementStatus);
        }

        return redirect()->route('login')->with('error', $result['message']);
    }

    /**
     * Show access denied page when application entitlement is missing/revoked.
     */
    public function accessDenied(): View
    {
        return view('pages.auth.access-denied');
    }

    /**
     * Log the user out of POSKESTREN and optionally Gate session.
     */
    public function logout(Request $request): RedirectResponse
    {
        $endSessionUrl = $this->authService->logout($request);

        if (! empty($endSessionUrl)) {
            return redirect()->away($endSessionUrl);
        }

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar dari sistem.');
    }
}
