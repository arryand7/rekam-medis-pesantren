<?php

namespace App\Services\Integration;

use App\Contracts\Integration\AttendanceIntegrationContract;
use App\DTOs\Integration\AttendanceHealthDispositionDTO;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * HTTP Client for SABIRA Absensi Staging / Sandbox Integration.
 */
class HttpAttendanceSandboxIntegration implements AttendanceIntegrationContract
{
    protected string $endpointUrl;

    protected ?string $apiKey;

    protected int $timeout;

    public function __construct()
    {
        $this->endpointUrl = (string) config('integration.attendance.endpoint_url', 'https://absensi-sandbox.sabira.id/api/v1/health-dispositions');
        $this->apiKey = config('integration.attendance.api_key');
        $this->timeout = (int) config('integration.attendance.timeout_seconds', 5);
    }

    public function publishDisposition(AttendanceHealthDispositionDTO $dto): array
    {
        $payload = $dto->toArray();

        // Runtime privacy defense-in-depth verification
        $this->assertPayloadCompliant($payload);

        try {
            $client = Http::timeout($this->timeout)->acceptJson();
            if ($this->apiKey) {
                $client = $client->withToken($this->apiKey);
            }

            $response = $client->withHeaders([
                'X-Poskestren-Event-Id' => $dto->eventId,
                'X-Idempotency-Key' => $dto->eventId,
            ])->post($this->endpointUrl, $payload);

            if ($response->successful()) {
                $ref = $response->json('data.reference_id')
                    ?? $response->json('reference_id')
                    ?? 'ABS-'.substr(md5($dto->eventId), 0, 10);

                return [
                    'success' => true,
                    'external_reference' => (string) $ref,
                    'status_code' => $response->status(),
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'external_reference' => null,
                'status_code' => $response->status(),
                'error' => "HTTP {$response->status()}: ".substr($response->body(), 0, 500),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'external_reference' => null,
                'status_code' => 504,
                'error' => 'Attendance sandbox transport error: '.$e->getMessage(),
            ];
        }
    }

    public function supersedeDisposition(string $originalEventId, AttendanceHealthDispositionDTO $newDto): array
    {
        $payload = $newDto->toArray();
        $payload['supersedes_event_id'] = $originalEventId;

        $this->assertPayloadCompliant($payload);

        try {
            $client = Http::timeout($this->timeout)->acceptJson();
            if ($this->apiKey) {
                $client = $client->withToken($this->apiKey);
            }

            $url = rtrim($this->endpointUrl, '/').'/supersede';
            $response = $client->withHeaders([
                'X-Poskestren-Event-Id' => $newDto->eventId,
                'X-Original-Event-Id' => $originalEventId,
                'X-Idempotency-Key' => $newDto->eventId,
            ])->post($url, $payload);

            if ($response->successful()) {
                $ref = $response->json('data.reference_id')
                    ?? $response->json('reference_id')
                    ?? 'ABS-SUP-'.substr(md5($newDto->eventId), 0, 10);

                return [
                    'success' => true,
                    'external_reference' => (string) $ref,
                    'status_code' => $response->status(),
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'external_reference' => null,
                'status_code' => $response->status(),
                'error' => "HTTP {$response->status()}: ".substr($response->body(), 0, 500),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'external_reference' => null,
                'status_code' => 504,
                'error' => 'Attendance sandbox supersede error: '.$e->getMessage(),
            ];
        }
    }

    public function revokeDisposition(string $eventId, string $reason): array
    {
        try {
            $client = Http::timeout($this->timeout)->acceptJson();
            if ($this->apiKey) {
                $client = $client->withToken($this->apiKey);
            }

            $url = rtrim($this->endpointUrl, '/')."/{$eventId}/revoke";
            $response = $client->post($url, [
                'event_id' => $eventId,
                'revocation_reason' => $reason,
                'revoked_at' => now()->toIso8601String(),
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'external_reference' => 'ABS-REV-'.$eventId,
                    'status_code' => $response->status(),
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'external_reference' => null,
                'status_code' => $response->status(),
                'error' => "HTTP {$response->status()}: ".substr($response->body(), 0, 500),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'external_reference' => null,
                'status_code' => 504,
                'error' => 'Attendance sandbox revoke error: '.$e->getMessage(),
            ];
        }
    }

    public function probeHealth(): array
    {
        $enabled = config('integration.attendance.enabled', false);

        try {
            $client = Http::timeout(2)->acceptJson();
            $healthUrl = rtrim($this->endpointUrl, '/').'/health';
            $response = $client->get($healthUrl);

            return [
                'driver' => 'sandbox',
                'enabled' => $enabled,
                'reachable' => $response->successful(),
                'message' => $response->successful()
                    ? 'Attendance sandbox endpoint reachable.'
                    : "Sandbox returned HTTP {$response->status()}.",
            ];
        } catch (Throwable $e) {
            return [
                'driver' => 'sandbox',
                'enabled' => $enabled,
                'reachable' => false,
                'message' => 'Attendance sandbox endpoint unreachable: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Defense-in-depth check forbidding medical / clinical attributes from outbound attendance payloads.
     *
     * @param  array<string, mixed>  $payload
     */
    public function assertPayloadCompliant(array $payload): void
    {

        $forbiddenKeys = [
            'diagnosis', 'diagnoses', 'icd10', 'icd_code', 'complaint', 'symptoms',
            'vital_signs', 'blood_pressure', 'heart_rate', 'temperature',
            'medicines', 'medications', 'prescriptions', 'allergies',
            'clinical_notes', 'assessment', 'referral_notes', 'advice',
        ];

        foreach ($forbiddenKeys as $key) {
            if (array_key_exists($key, $payload)) {
                throw new \InvalidArgumentException("Privacy breach blocked: Forbidden clinical key '{$key}' cannot be transmitted to Attendance system.");
            }
        }
    }
}
