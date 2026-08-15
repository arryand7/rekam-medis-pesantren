<?php

namespace App\Services\Gate;

use App\Contracts\GateClientContract;
use App\DTOs\GateUserDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class HttpGateClient implements GateClientContract
{
    public function fetchUsers(int $page = 1, int $perPage = 50): array
    {
        $baseUrl = rtrim(config('gate.base_url', 'https://gate.sabira.id'), '/');
        $endpoint = config('gate.endpoints.users', '/api/v1/users');
        $timeout = (int) config('gate.http.timeout', 5);

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'X-Client-ID' => config('gate.client_id', ''),
                    'X-Client-Secret' => config('gate.client_secret', ''),
                    'Accept' => 'application/json',
                ])
                ->get("{$baseUrl}{$endpoint}", [
                    'page' => $page,
                    'per_page' => $perPage,
                ]);

            if (! $response->successful()) {
                throw new RuntimeException('Gagal mengambil daftar pengguna dari Gate: HTTP '.$response->status());
            }

            $json = $response->json();
            $data = $json['data'] ?? $json;

            $dtos = [];
            foreach ($data as $item) {
                if (is_array($item)) {
                    $dtos[] = GateUserDTO::fromArray($item);
                }
            }

            return [
                'data' => $dtos,
                'page' => (int) ($json['page'] ?? $page),
                'total_pages' => (int) ($json['total_pages'] ?? 1),
                'total_items' => (int) ($json['total_items'] ?? count($dtos)),
            ];
        } catch (Throwable $e) {
            Log::error('Gate fetchUsers exception', ['exception_class' => $e::class]);
            throw new RuntimeException('Koneksi sinkronisasi Gate terganggu.', 0, $e);
        }
    }

    public function fetchUserById(string $gateUserId): ?GateUserDTO
    {
        $baseUrl = rtrim(config('gate.base_url', 'https://gate.sabira.id'), '/');
        $endpoint = config('gate.endpoints.users', '/api/v1/users');
        $timeout = (int) config('gate.http.timeout', 5);

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'X-Client-ID' => config('gate.client_id', ''),
                    'X-Client-Secret' => config('gate.client_secret', ''),
                    'Accept' => 'application/json',
                ])
                ->get("{$baseUrl}{$endpoint}/{$gateUserId}");

            if ($response->status() === 404) {
                return null;
            }

            if (! $response->successful()) {
                throw new RuntimeException('Gagal mengambil data user Gate: HTTP '.$response->status());
            }

            return GateUserDTO::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Gate fetchUserById exception', ['exception_class' => $e::class]);

            return null;
        }
    }

    public function ping(): bool
    {
        $baseUrl = rtrim(config('gate.base_url', 'https://gate.sabira.id'), '/');
        $endpoint = config('gate.endpoints.health', '/health');
        $timeout = 3;

        try {
            $response = Http::timeout($timeout)->get("{$baseUrl}{$endpoint}");

            return $response->successful();
        } catch (Throwable) {
            return false;
        }
    }
}
