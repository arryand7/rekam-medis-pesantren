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
        $endpoint = config('gate.endpoints.users', '/api/provisioning/users');
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
            // Gate SSO mengembalikan { users: [...], total_users: N, ... }
            $data = $json['users'] ?? $json['data'] ?? $json;

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
                'total_items' => (int) ($json['total_users'] ?? $json['total_items'] ?? count($dtos)),
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
        $endpoint = config('gate.endpoints.users', '/api/provisioning/users');
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

            $json = $response->json();
            $userData = $json['user'] ?? $json;

            return GateUserDTO::fromArray($userData);
        } catch (Throwable $e) {
            Log::error('Gate fetchUserById exception', ['exception_class' => $e::class]);

            return null;
        }
    }

    /**
     * Download foto dari Temporary Signed URL Gate SSO.
     * Mengembalikan binary content gambar, atau null jika gagal.
     */
    public function downloadPhoto(string $signedUrl): ?string
    {
        try {
            $response = Http::timeout(15)->get($signedUrl);

            if (! $response->successful()) {
                Log::warning('Gate downloadPhoto failed', [
                    'status' => $response->status(),
                    'url' => substr($signedUrl, 0, 80).'...',
                ]);

                return null;
            }

            $contentType = $response->header('Content-Type') ?? '';
            if (! str_starts_with($contentType, 'image/')) {
                Log::warning('Gate downloadPhoto: response is not an image', ['content_type' => $contentType]);

                return null;
            }

            return $response->body();
        } catch (Throwable $e) {
            Log::error('Gate downloadPhoto exception', ['exception_class' => $e::class]);

            return null;
        }
    }

    public function ping(): bool
    {
        $settings = $this->configuration->get();
        $baseUrl = rtrim((string) $settings['base_url'], '/');
        // Gate SSO menggunakan /api/provisioning/me sebagai health+credential check
        $endpoint = config('gate.endpoints.provisioning_me', '/api/provisioning/me');
        try {
            $response = $this->request($settings, 5)
                ->withHeaders([
                    'X-Client-ID' => $settings['client_id'],
                    'X-Client-Secret' => $settings['client_secret'],
                    'Accept' => 'application/json',
                ])
                ->get("{$baseUrl}{$endpoint}");

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
