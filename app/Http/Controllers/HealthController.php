<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    /**
     * Application Liveness Endpoint (/health).
     * Exposes high-level liveness status without exposing sensitive environment internals.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'app' => (string) config('app.name', 'SABIRA POSKESTREN Health'),
            'environment' => (string) config('app.env', 'production'),
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    /**
     * Application Readiness Probe Endpoint (/health/ready).
     * Verifies critical subsystems (Database, Cache, Private Storage, and Integration configurations).
     */
    public function ready(): JsonResponse
    {
        $dbStatus = false;
        try {
            DB::connection()->getPdo();
            $dbStatus = true;
        } catch (Throwable $e) {
            $dbStatus = false;
        }

        $cacheStatus = false;
        try {
            Cache::put('readiness_probe', true, 10);
            $cacheStatus = Cache::get('readiness_probe') === true;
        } catch (Throwable $e) {
            $cacheStatus = false;
        }

        $privateStoragePath = storage_path('app/private');
        $storageStatus = is_dir($privateStoragePath) ? is_writable($privateStoragePath) : is_writable(storage_path('app'));

        $isReady = $dbStatus && $cacheStatus && $storageStatus;

        return response()->json([
            'status' => $isReady ? 'ready' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'dependencies' => [
                'database' => $dbStatus ? 'connected' : 'unreachable',
                'cache' => $cacheStatus ? 'operational' : 'failed',
                'private_storage' => $storageStatus ? 'writable' : 'unwritable',
            ],
            'integrations' => [
                'gate' => [
                    'driver' => (string) config('gate.driver', 'fake'),
                    'sso_enabled' => (bool) config('gate.sso_enabled', false),
                    'sync_apply_enabled' => (bool) config('gate.sync_apply_enabled', false),
                ],
                'attendance' => [
                    'driver' => (string) config('integration.attendance.driver', 'fake'),
                    'enabled' => (bool) config('integration.attendance.enabled', false),
                ],
            ],
        ], $isReady ? 200 : 503);
    }
}
