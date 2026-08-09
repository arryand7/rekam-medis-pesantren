<?php

namespace App\Providers;

use App\Contracts\GateClientContract;
use App\Contracts\Integration\AttendanceIntegrationContract;
use App\Services\Gate\FakeGateClientService;
use App\Services\Integration\FakeAttendanceIntegration;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GateClientContract::class, FakeGateClientService::class);
        $this->app->bind(
            AttendanceIntegrationContract::class,
            FakeAttendanceIntegration::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('view-clinical-dashboard', fn ($user) => $user->hasPermission('view-clinical-dashboard'));
        Gate::define('view-management-dashboard', fn ($user) => $user->hasPermission('view-management-dashboard'));
        Gate::define('view-operational-dashboard', fn ($user) => $user->hasPermission('view-operational-dashboard'));
        Gate::define('view-health-reports', fn ($user) => $user->hasPermission('view-health-reports'));
        Gate::define('export-health-reports', fn ($user) => $user->hasPermission('export-health-reports'));
    }
}
