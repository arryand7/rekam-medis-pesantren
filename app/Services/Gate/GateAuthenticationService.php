<?php

namespace App\Services\Gate;

use App\Contracts\GateOidcClientContract;
use App\DTOs\GateApplicationEntitlementDTO;
use App\DTOs\GateUserInfoDTO;
use App\Models\Patient;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SsoConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GateAuthenticationService
{
    public function __construct(
        protected GateOidcClientContract $oidcClient,
        protected SsoConfigurationService $configuration
    ) {}

    /**
     * Generate Gate SSO redirect URL and save state in session.
     */
    public function initiateLogin(Request $request): string
    {
        $state = Str::random(40);
        $nonce = Str::random(40);

        $request->session()->put('gate_auth_state', $state);
        $request->session()->put('gate_auth_nonce', $nonce);

        AuditLogService::log(
            action: 'gate_login.initiated',
            subjectType: 'Session',
            subjectId: null,
            before: null,
            after: ['state_created' => true],
            reason: 'Inisiasi login Gate SSO'
        );

        return $this->oidcClient->getAuthorizationUrl($state, $nonce);
    }

    /**
     * Handle OAuth / OIDC callback, validate tokens, enforce entitlement, and project identity.
     *
     * @return array{user: ?User, entitlement: ?GateApplicationEntitlementDTO, status: string, message: string}
     */
    public function handleCallback(Request $request): array
    {
        $sessionState = $request->session()->pull('gate_auth_state');
        $code = (string) $request->query('code');
        $state = (string) $request->query('state');
        $error = $request->query('error');

        if (! empty($error)) {
            AuditLogService::log(
                action: 'gate_login.failed',
                subjectType: 'Session',
                subjectId: null,
                before: null,
                after: ['error' => $error],
                reason: 'Login Gate SSO ditolak oleh provider: '.$error
            );

            return [
                'user' => null,
                'entitlement' => null,
                'status' => 'provider_error',
                'message' => 'Otorisasi Gate SSO dibatalkan atau ditolak.',
            ];
        }

        if (empty($state) || empty($sessionState) || ! hash_equals($sessionState, $state)) {
            AuditLogService::log(
                action: 'gate_login.failed',
                subjectType: 'Session',
                subjectId: null,
                before: null,
                after: ['state_validation' => 'mismatch'],
                reason: 'State validation mismatch pada Gate callback (CSRF/replay risk)'
            );

            return [
                'user' => null,
                'entitlement' => null,
                'status' => 'invalid_state',
                'message' => 'Validasi sesi Gate kadaluarsa atau tidak valid. Silakan coba login kembali.',
            ];
        }

        if (empty($code)) {
            return [
                'user' => null,
                'entitlement' => null,
                'status' => 'missing_code',
                'message' => 'Kode otorisasi Gate tidak ditemukan.',
            ];
        }

        // 1. Exchange Code for Tokens
        try {
            $tokenResponse = $this->oidcClient->exchangeAuthorizationCode($code);
        } catch (RuntimeException $e) {
            AuditLogService::log(
                action: 'gate_login.failed',
                subjectType: 'Session',
                subjectId: null,
                before: null,
                after: ['failure_type' => 'token_exchange'],
                reason: 'Pertukaran token Gate gagal'
            );

            return [
                'user' => null,
                'entitlement' => null,
                'status' => 'token_exchange_failed',
                'message' => 'Gagal menukarkan token otorisasi Gate.',
            ];
        }

        try {
            // 2. Fetch UserInfo
            $userInfo = $this->oidcClient->fetchUserInfo($tokenResponse->accessToken);

            // 3. Enforce Application Entitlement
            $appCode = (string) $this->configuration->get()['app_code'];
            $entitlement = $this->oidcClient->fetchApplicationEntitlement(
                $tokenResponse->accessToken,
                $userInfo->gateUserId,
                $appCode
            );
        } catch (Throwable) {
            AuditLogService::log(
                action: 'gate_login.failed',
                subjectType: 'Session',
                subjectId: null,
                before: null,
                after: ['failure_type' => 'userinfo_or_entitlement'],
                reason: 'Validasi identitas atau entitlement Gate gagal'
            );

            return [
                'user' => null,
                'entitlement' => null,
                'status' => 'provider_validation_failed',
                'message' => 'Gate tidak dapat memvalidasi identitas atau hak akses aplikasi.',
            ];
        }

        if (! $entitlement->isAllowed()) {
            AuditLogService::log(
                action: 'gate_login.access_denied',
                subjectType: 'GateUser',
                subjectId: $userInfo->gateUserId,
                before: null,
                after: $entitlement->toArray(),
                reason: "Akses aplikasi {$appCode} tidak diizinkan untuk {$userInfo->name} (Status: {$entitlement->status})"
            );

            return [
                'user' => null,
                'entitlement' => $entitlement,
                'status' => 'entitlement_denied',
                'message' => 'Akun Anda tidak memiliki hak akses (entitlement) ke aplikasi POSKESTREN Health.',
            ];
        }

        // 4. Project Person and User into local database atomically
        $user = $this->projectIdentity($userInfo, $entitlement);

        // 5. Establish Session
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('gate_id_token', $tokenResponse->idToken);
        $request->session()->put('gate_entitlement_verified_at', now()->timestamp);

        // Update user last login
        $user->update(['last_login_at' => now()]);

        AuditLogService::log(
            action: 'gate_login.succeeded',
            subjectType: 'User',
            subjectId: $user->id,
            before: null,
            after: [
                'gate_user_id' => $userInfo->gateUserId,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
            ],
            reason: 'Pengguna berhasil login via Gate SSO'
        );

        return [
            'user' => $user,
            'entitlement' => $entitlement,
            'status' => 'success',
            'message' => 'Login berhasil.',
        ];
    }

    /**
     * Atomically project Gate user info into local Person, User, and Patient models.
     */
    public function projectIdentity(GateUserInfoDTO $userInfo, GateApplicationEntitlementDTO $entitlement): User
    {
        return DB::transaction(function () use ($userInfo) {
            // Find existing person by gate_user_id with row lock
            $person = Person::where('gate_user_id', $userInfo->gateUserId)->lockForUpdate()->first();

            if (! $person) {
                // If not found by gate_user_id, check approved mappings
                $mapping = DB::table('gate_identity_mappings')
                    ->where('gate_user_id', $userInfo->gateUserId)
                    ->where('status', 'approved')
                    ->first();

                if ($mapping) {
                    $person = Person::where('id', $mapping->person_id)->lockForUpdate()->first();
                }
            }

            $isNewPerson = false;
            if (! $person) {
                $person = new Person;
                $person->gate_user_id = $userInfo->gateUserId;
                $isNewPerson = true;
            }

            // Update only authoritative projection fields (NEVER touch medical records/diagnoses)
            $person->name = $userInfo->name;
            if (! empty($userInfo->nik)) {
                $person->nik = $userInfo->nik;
            }
            if (! empty($userInfo->nisNip)) {
                $person->nis_nip = $userInfo->nisNip;
            }
            $person->user_type = $userInfo->userType;
            if (! empty($userInfo->gender)) {
                $person->gender = $this->normalizeGender($userInfo->gender);
            }
            $person->phone = $userInfo->phone;
            $person->email = $userInfo->email;
            $person->source_status = $userInfo->sourceStatus;
            $person->checksum = $userInfo->checksum;
            $person->source_version = $userInfo->sourceVersion;
            $person->source_updated_at = $userInfo->sourceUpdatedAt ? now()->parse($userInfo->sourceUpdatedAt) : now();
            $person->synced_at = now();
            $person->save();

            // Create or update local User
            $user = User::where('person_id', $person->id)->lockForUpdate()->first()
                ?? ($person->email ? User::where('email', $person->email)->lockForUpdate()->first() : null);

            if (! $user) {
                $user = new User;
                $user->person_id = $person->id;
                $user->password = bcrypt(Str::random(32)); // Opaque unusable random password
            }

            $user->person_id = $person->id;
            $user->name = $person->name;
            $user->email = $person->email ?? "{$userInfo->gateUserId}@gate.example.invalid";
            $user->is_active = ($userInfo->sourceStatus === 'active');
            $user->save();

            // Sync mapped application roles if provided by Gate
            $this->applyRoleMapping($user, $userInfo->appRoles);

            // Create Patient record if person is human-eligible and patient record does not exist yet
            if ($person->isHumanPatientEligible()) {
                Patient::createOrFindForPerson($person);
            }

            AuditLogService::log(
                action: $isNewPerson ? 'gate_user.projection_created' : 'gate_user.projection_updated',
                subjectType: 'Person',
                subjectId: $person->id,
                before: null,
                after: $person->toArray(),
                reason: "Proyeksi identitas Gate untuk {$person->name}"
            );

            return $user;
        });
    }

    /**
     * Normalize gender value to single character (L / P).
     */
    protected function normalizeGender(?string $gender): ?string
    {
        if (empty($gender)) {
            return null;
        }

        $g = strtolower(trim($gender));
        if ($g === 'l' || str_starts_with($g, 'laki') || $g === 'male' || $g === 'm') {
            return 'L';
        }
        if ($g === 'p' || str_starts_with($g, 'perempuan') || $g === 'female' || $g === 'f') {
            return 'P';
        }

        return substr(strtoupper($gender), 0, 1);
    }

    /**
     * Map Gate application roles to local system roles based on explicit mapping.
     *
     * @param  list<string>  $gateRoles
     */
    protected function applyRoleMapping(User $user, array $gateRoles): void
    {
        if (empty($gateRoles)) {
            return;
        }

        $mapping = config('gate.role_mapping', []);
        $localRoleNames = [];

        foreach ($gateRoles as $roleClaim) {
            if (isset($mapping[$roleClaim])) {
                $localRoleNames[] = $mapping[$roleClaim];
            }
        }

        if (! empty($localRoleNames)) {
            $roles = Role::whereIn('name', $localRoleNames)->get();
            if ($roles->isNotEmpty()) {
                $user->roles()->syncWithoutDetaching($roles->pluck('id')->toArray());
            }
        }
    }

    /**
     * Perform local and optional remote Gate logout.
     */
    public function logout(Request $request): ?string
    {
        $idToken = $request->session()->get('gate_id_token');
        $user = Auth::user();

        if ($user) {
            AuditLogService::log(
                action: 'gate_logout',
                subjectType: 'User',
                subjectId: $user->id,
                before: null,
                after: null,
                reason: 'Pengguna melakukan logout sesi'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (! $this->configuration->get()['sso_enabled']) {
            return null;
        }

        $postLogoutRedirectUri = route('login');

        return $this->oidcClient->getEndSessionUrl($idToken, $postLogoutRedirectUri);
    }
}
