<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    /**
     * Application Health Check Endpoint.
     */
    public function __invoke(): JsonResponse
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
            Cache::put('health_check', true, 10);
            $cacheStatus = Cache::get('health_check') === true;
        } catch (Throwable $e) {
            $cacheStatus = false;
        }

        $storageStatus = is_writable(storage_path());

        $isHealthy = $dbStatus && $cacheStatus && $storageStatus;

        return response()->json([
            'status' => $isHealthy ? 'ok' : 'degraded',
            'app' => config('app.name', 'SABIRA POSKESTREN Health'),
            'timestamp' => now()->toIso8601String(),
            'timezone' => config('app.timezone'),
            'checks' => [
                'database' => $dbStatus ? 'ok' : 'failed',
                'cache' => $cacheStatus ? 'ok' : 'failed',
                'storage' => $storageStatus ? 'ok' : 'failed',
            ],
        ], $isHealthy ? 200 : 503);
    }
}
