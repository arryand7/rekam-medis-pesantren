<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Gate\GateAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GateOidcAuthController extends Controller
{
    public function __construct(
        protected GateAuthenticationService $authService
    ) {}

    /**
     * Show login page or redirect to Gate authorization URL.
     */
    public function login(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // If sso is enabled or user requests immediate redirect
        if ($request->has('redirect') || config('gate.sso_enabled', false)) {
            $redirectUrl = $this->authService->initiateLogin($request);

            return redirect()->away($redirectUrl);
        }

        return view('pages.auth.login');
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
