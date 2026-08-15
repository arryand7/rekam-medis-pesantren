<?php

namespace App\Providers;

use App\Contracts\GateClientContract;
use App\Contracts\GateOidcClientContract;
use App\Contracts\Integration\AttendanceIntegrationContract;
use App\Models\IntegrationIdentityConflict;
use App\Models\IntegrationOutboxEvent;
use App\Policies\IntegrationIdentityConflictPolicy;
use App\Policies\IntegrationOutboxPolicy;
use App\Services\Gate\FakeGateClientService;
use App\Services\Gate\FakeGateOidcClient;
use App\Services\Gate\HttpGateClient;
use App\Services\Gate\HttpGateOidcClient;
use App\Services\Integration\FakeAttendanceIntegration;
use App\Services\Integration\HttpAttendanceSandboxIntegration;
use App\Services\SsoConfigurationService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            GateOidcClientContract::class,
            function ($app) {
                return $app->make(SsoConfigurationService::class)->get()['driver'] === 'http'
                    ? $app->make(HttpGateOidcClient::class)
                    : $app->make(FakeGateOidcClient::class);
            }
        );

        $this->app->bind(
            GateClientContract::class,
            function ($app) {
                return $app->make(SsoConfigurationService::class)->get()['driver'] === 'http'
                    ? $app->make(HttpGateClient::class)
                    : $app->make(FakeGateClientService::class);
            }
        );

        $this->app->bind(
            AttendanceIntegrationContract::class,
            function () {
                $driver = config('integration.attendance.driver', 'fake');

                return match ($driver) {
                    'sandbox', 'http' => new HttpAttendanceSandboxIntegration,
                    default => new FakeAttendanceIntegration,
                };
            }
        );

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability): ?bool {
            if ($user && method_exists($user, 'hasPermission') && $user->hasPermission($ability)) {
                return true;
            }

            return null;
        });

        Gate::define('view-clinical-dashboard', fn ($user) => $user->hasPermission('view-clinical-dashboard'));
        Gate::define('view-pharmacy-dashboard', fn ($user) => $user->hasPermission('view-pharmacy-dashboard') || $user->hasPermission('view-pharmacy-inventory'));
        Gate::define('view-management-dashboard', fn ($user) => $user->hasPermission('view-management-dashboard'));
        Gate::define('view-operational-dashboard', fn ($user) => $user->hasPermission('view-operational-dashboard'));
        Gate::define('view-health-reports', fn ($user) => $user->hasPermission('view-health-reports'));
        Gate::define('export-health-reports', fn ($user) => $user->hasPermission('export-health-reports'));
        Gate::define('view-gate-sync', fn ($user) => $user->hasPermission('view-gate-sync'));
        Gate::define('execute-gate-sync-apply', fn ($user) => $user->hasPermission('execute-gate-sync-apply'));
        Gate::define('manage-identity-mappings', fn ($user) => $user->hasPermission('manage-identity-mappings'));
        Gate::define('view-gate-reconciliation', fn ($user) => $user->hasPermission('view-gate-reconciliation'));
        Gate::define('manage-system-settings', fn ($user) => $user->hasPermission('manage-system-settings'));
        Gate::define('manage-sso-settings', fn ($user) => $user->isSuperAdmin());

        Gate::policy(IntegrationOutboxEvent::class, IntegrationOutboxPolicy::class);
        Gate::policy(IntegrationIdentityConflict::class, IntegrationIdentityConflictPolicy::class);
    }
}
