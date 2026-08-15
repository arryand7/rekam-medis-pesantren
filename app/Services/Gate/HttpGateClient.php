<?php

namespace App\Services\Gate;

use App\Contracts\GateClientContract;
use App\DTOs\GateUserDTO;
use App\Services\SsoConfigurationService;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class HttpGateClient implements GateClientContract
{
    public function __construct(
        private readonly SsoConfigurationService $configuration
    ) {}

    public function fetchUsers(int $page = 1, int $perPage = 50): array
    {
        $settings = $this->configuration->get();
        $baseUrl = rtrim((string) $settings['base_url'], '/');
        $endpoint = config('gate.endpoints.users', '/api/v1/users');
        try {
            $response = $this->request($settings)
                ->withHeaders([
                    'X-Client-ID' => $settings['client_id'],
                    'X-Client-Secret' => $settings['client_secret'],
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
        $settings = $this->configuration->get();
        $baseUrl = rtrim((string) $settings['base_url'], '/');
        $endpoint = config('gate.endpoints.users', '/api/v1/users');
        try {
            $response = $this->request($settings)
                ->withHeaders([
                    'X-Client-ID' => $settings['client_id'],
                    'X-Client-Secret' => $settings['client_secret'],
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
        $settings = $this->configuration->get();
        $baseUrl = rtrim((string) $settings['base_url'], '/');
        $endpoint = config('gate.endpoints.health', '/health');
        try {
            $response = $this->request($settings, 3)->get("{$baseUrl}{$endpoint}");

            return $response->successful();
        } catch (Throwable) {
            return false;
        }
    }

    /** @param array<string, mixed> $settings */
    private function request(array $settings, ?int $timeout = null): PendingRequest
    {
        $request = Http::timeout($timeout ?? (int) $settings['http_timeout']);
        $attempts = (int) $settings['retry_attempts'];

        return $attempts > 0
            ? $request->retry($attempts, (int) $settings['retry_backoff_ms'], throw: false)
            : $request;
    }
}
