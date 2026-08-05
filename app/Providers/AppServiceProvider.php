<?php

namespace App\Providers;

use App\Contracts\GateClientContract;
use App\Services\Gate\FakeGateClientService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GateClientContract::class, FakeGateClientService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
