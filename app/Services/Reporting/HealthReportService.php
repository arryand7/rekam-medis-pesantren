<?php

namespace App\Services\Reporting;

use App\Models\HealthcarePartner;
use App\Models\IntegrationDeliveryAttempt;
use App\Models\MedicalVisit;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\ObservationEpisode;
use App\Models\Patient;
use App\Models\Person;
use App\Models\Referral;
use App\Models\StockLocation;
use App\Models\User;
use App\Models\VisitDischarge;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Service providing core clinical census and management reports with filtering, pagination, summary KPIs, and secure streaming export.
 */
class HealthReportService
{
    /**
     * Whitelist of explicitly supported report types.
     *
     * @var array<int, string>
     */
    public const SUPPORTED_REPORT_TYPES = [
        'visit_census',
        'observation_census',
        'referral_census',
        'discharge_followup',
        'pharmacy_stock',
        'integration_delivery',
    ];

    /**
     * Get paginated visit census report.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getVisitCensus(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MedicalVisit::with(['patient.person', 'receivingOfficer'])
            ->latest('created_at');

        $this->applyDateFilters($query, $filters);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get paginated observation census report.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getObservationCensus(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = ObservationEpisode::with(['medicalVisit.patient.person', 'responsibleOfficer'])
            ->latest('created_at');

        $this->applyDateFilters($query, $filters);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get paginated external referral census report.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getReferralCensus(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Referral::with(['medicalVisit.patient.person', 'healthcarePartner'])
            ->latest('created_at');

        $this->applyDateFilters($query, $filters);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get paginated discharge and follow-up report.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getDischargeReport(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = VisitDischarge::with(['medicalVisit.patient.person'])
            ->latest('created_at');

        $this->applyDateFilters($query, $filters);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get paginated pharmacy stock and expiry report.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPharmacyStockReport(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MedicineBatch::with(['medicine', 'location'])
            ->orderBy('expiry_date', 'asc');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('medicine', function ($mq) use ($search) {
                        $mq->where('brand_name', 'like', "%{$search}%")
                            ->orWhere('generic_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get paginated integration delivery outbox report.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getIntegrationDeliveryReport(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = IntegrationDeliveryAttempt::latest('created_at');

        $this->applyDateFilters($query, $filters);

        if (! empty($filters['status'])) {
            $query->where('result', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get summary KPI strip for a specific report type applying the EXACT SAME filters as the table.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getReportSummary(string $reportType, array $filters = []): array
    {
        switch ($reportType) {
            case 'visit_census':
                $query = MedicalVisit::query();
                $this->applyDateFilters($query, $filters);
                if (! empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }

                return [
                    'total_visits' => (clone $query)->count(),
                    'completed_visits' => (clone $query)->where('status', 'completed')->count(),
                    'waiting_visits' => (clone $query)->whereIn('status', ['registered', 'waiting_assessment'])->count(),
                ];

            case 'observation_census':
                $query = ObservationEpisode::query();
                $this->applyDateFilters($query, $filters);
                if (! empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }

                return [
                    'total_episodes' => (clone $query)->count(),
                    'active_episodes' => (clone $query)->where('status', 'active')->count(),
                    'completed_episodes' => (clone $query)->where('status', 'completed')->count(),
                ];

            case 'referral_census':
                $query = Referral::query();
                $this->applyDateFilters($query, $filters);
                if (! empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }

                return [
                    'total_referrals' => (clone $query)->count(),
                    'emergency_referrals' => (clone $query)->where('urgency', 'emergency')->count(),
                    'returned_referrals' => (clone $query)->where('status', 'returned')->count(),
                ];

            case 'discharge_followup':
                $query = VisitDischarge::query();
                $this->applyDateFilters($query, $filters);
                if (! empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }

                return [
                    'total_discharges' => (clone $query)->count(),
                    'follow_up_required' => (clone $query)->where('follow_up_required', true)->count(),
                    'bed_rest_recommended' => (clone $query)->where('activity_recommendation', 'bed_rest')->count(),
                ];

            case 'pharmacy_stock':
                $query = MedicineBatch::query();
                if (! empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }
                if (! empty($filters['search'])) {
                    $search = $filters['search'];
                    $query->where(function ($q) use ($search) {
                        $q->where('batch_number', 'like', "%{$search}%")
                            ->orWhereHas('medicine', function ($mq) use ($search) {
                                $mq->where('brand_name', 'like', "%{$search}%")
                                    ->orWhere('generic_name', 'like', "%{$search}%");
                            });
                    });
                }

                $warningDays = (int) config('pharmacy.expiry_warning_days', 30);
                $threshold = now()->addDays($warningDays)->toDateString();

                return [
                    'total_batches' => (clone $query)->count(),
                    'depleted_batches' => (clone $query)->where('current_quantity', '<=', 0)->count(),
                    'near_expiry_batches' => (clone $query)->where('expiry_date', '<=', $threshold)->where('current_quantity', '>', 0)->count(),
                ];

            case 'integration_delivery':
                $query = IntegrationDeliveryAttempt::query();
                $this->applyDateFilters($query, $filters);
                if (! empty($filters['status'])) {
                    $query->where('result', $filters['status']);
                }

                return [
                    'total_deliveries' => (clone $query)->count(),
                    'successful_deliveries' => (clone $query)->where('result', 'success')->count(),
                    'failed_deliveries' => (clone $query)->whereIn('result', ['failed', 'dead_letter', 'retry_exhausted'])->count(),
                ];

            default:
                return [];
        }
    }

    /**
     * Stream CSV export directly to HTTP response with UTF-8 BOM, formula sanitization, audit metadata header, and chunking.
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportCsv(string $reportType, array $filters = [], ?User $user = null): StreamedResponse
    {
        if (! in_array($reportType, self::SUPPORTED_REPORT_TYPES, true)) {
            abort(422, 'Tipe laporan tidak valid');
        }

        $sanitizedType = preg_replace('/[^a-zA-Z0-9_-]/', '_', $reportType) ?: 'report';
        $filename = 'poskestren_'.$sanitizedType.'_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($reportType, $filters, $user) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // Write UTF-8 BOM for Microsoft Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Write metadata header comment block
            $this->writeCsvRow($handle, ['# LAPORAN POSKESTREN SABIRA HEALTH']);
            $this->writeCsvRow($handle, ['# Tipe Laporan', ucwords(str_replace('_', ' ', $reportType))]);
            $this->writeCsvRow($handle, ['# Diekspor Pada', now()->format('d/m/Y H:i:s')]);
            $this->writeCsvRow($handle, ['# Petugas Pengekspor', $user ? $user->name : 'Sistem']);
            if (! empty($filters['start_date']) || ! empty($filters['end_date'])) {
                $this->writeCsvRow($handle, ['# Filter Rentang', ($filters['start_date'] ?? 'Awal').' s/d '.($filters['end_date'] ?? 'Akhir')]);
            }
            if (! empty($filters['status'])) {
                $this->writeCsvRow($handle, ['# Filter Status', $filters['status']]);
            }
            $this->writeCsvRow($handle, []); // Empty line separator

            switch ($reportType) {
                case 'visit_census':
                    $this->streamVisitCensus($handle, $filters);
                    break;
                case 'observation_census':
                    $this->streamObservationCensus($handle, $filters);
                    break;
                case 'referral_census':
                    $this->streamReferralCensus($handle, $filters);
                    break;
                case 'discharge_followup':
                    $this->streamDischargeReport($handle, $filters);
                    break;
                case 'pharmacy_stock':
                    $this->streamPharmacyStockReport($handle, $filters);
                    break;
                case 'integration_delivery':
                    $this->streamIntegrationDeliveryReport($handle, $filters);
                    break;
                default:
                    $this->writeCsvRow($handle, ['Error', 'Tipe laporan tidak valid']);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * @param  resource  $handle
     * @param  array<string, mixed>  $filters
     */
    protected function streamVisitCensus($handle, array $filters): void
    {
        $this->writeCsvRow($handle, ['Nomor Kunjungan', 'Tanggal', 'Nama Pasien', 'No. RM', 'Keluhan Utama', 'Status', 'Petugas Penerima']);

        $query = MedicalVisit::with(['patient.person', 'receivingOfficer'])->latest('created_at');
        $this->applyDateFilters($query, $filters);
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->chunk(100, function ($visits) use ($handle) {
            foreach ($visits as $visit) {
                /** @var Patient|null $patient */
                $patient = $visit->patient;
                /** @var Person|null $person */
                $person = $patient?->person;
                /** @var User|null $officer */
                $officer = $visit->receivingOfficer;

                $this->writeCsvRow($handle, [
                    $visit->visit_number,
                    $visit->created_at ? $visit->created_at->format('d/m/Y H:i') : '-',
                    $person ? $person->name : 'Santri/Warga',
                    $patient ? $patient->patient_number : '-',
                    $visit->chief_complaint,
                    $visit->status,
                    $officer ? $officer->name : '-',
                ]);
            }
        });
    }

    /**
     * @param  resource  $handle
     * @param  array<string, mixed>  $filters
     */
    protected function streamObservationCensus($handle, array $filters): void
    {
        $this->writeCsvRow($handle, ['Nomor Kunjungan', 'Nama Pasien', 'Tempat Tidur', 'Alasan Observasi', 'Waktu Masuk', 'Waktu Keluar', 'Status', 'Petugas Jaga']);

        $query = ObservationEpisode::with(['medicalVisit.patient.person', 'responsibleOfficer'])->latest('created_at');
        $this->applyDateFilters($query, $filters);
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->chunk(100, function ($episodes) use ($handle) {
            foreach ($episodes as $ep) {
                /** @var MedicalVisit|null $visit */
                $visit = $ep->medicalVisit;
                /** @var Patient|null $patient */
                $patient = $visit?->patient;
                /** @var Person|null $person */
                $person = $patient?->person;
                /** @var User|null $officer */
                $officer = $ep->responsibleOfficer;

                $this->writeCsvRow($handle, [
                    $visit ? $visit->visit_number : '-',
                    $person ? $person->name : 'Santri/Warga',
                    $ep->bed_label ?? 'Ruang Observasi',
                    $ep->reason,
                    $ep->started_at ? Carbon::parse($ep->started_at)->format('d/m/Y H:i') : '-',
                    $ep->ended_at ? Carbon::parse($ep->ended_at)->format('d/m/Y H:i') : '-',
                    $ep->status,
                    $officer ? $officer->name : '-',
                ]);
            }
        });
    }

    /**
     * @param  resource  $handle
     * @param  array<string, mixed>  $filters
     */
    protected function streamReferralCensus($handle, array $filters): void
    {
        $this->writeCsvRow($handle, ['No. Rujukan', 'No. Kunjungan', 'Nama Pasien', 'Faskes Tujuan', 'Urgensi', 'Alasan Rujukan', 'Waktu Berangkat', 'Status']);

        $query = Referral::with(['medicalVisit.patient.person', 'healthcarePartner'])->latest('created_at');
        $this->applyDateFilters($query, $filters);
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->chunk(100, function ($referrals) use ($handle) {
            foreach ($referrals as $ref) {
                /** @var MedicalVisit|null $visit */
                $visit = $ref->medicalVisit;
                /** @var Patient|null $patient */
                $patient = $visit?->patient;
                /** @var Person|null $person */
                $person = $patient?->person;
                /** @var HealthcarePartner|null $partner */
                $partner = $ref->healthcarePartner;

                $this->writeCsvRow($handle, [
                    $ref->referral_number,
                    $visit ? $visit->visit_number : '-',
                    $person ? $person->name : 'Santri/Warga',
                    $partner ? $partner->name : '-',
                    $ref->urgency,
                    $ref->reason,
                    $ref->departed_at ? Carbon::parse($ref->departed_at)->format('d/m/Y H:i') : '-',
                    $ref->status,
                ]);
            }
        });
    }

    /**
     * @param  resource  $handle
     * @param  array<string, mixed>  $filters
     */
    protected function streamDischargeReport($handle, array $filters): void
    {
        $this->writeCsvRow($handle, ['No. Kunjungan', 'Nama Pasien', 'Tipe Kepulangan', 'Tujuan', 'Anjuran Aktivitas', 'Perlu Kontrol', 'Tanggal Kontrol', 'Status']);

        $query = VisitDischarge::with(['medicalVisit.patient.person'])->latest('created_at');
        $this->applyDateFilters($query, $filters);
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->chunk(100, function ($discharges) use ($handle) {
            foreach ($discharges as $disc) {
                /** @var MedicalVisit|null $visit */
                $visit = $disc->medicalVisit;
                /** @var Patient|null $patient */
                $patient = $visit?->patient;
                /** @var Person|null $person */
                $person = $patient?->person;

                $this->writeCsvRow($handle, [
                    $visit ? $visit->visit_number : '-',
                    $person ? $person->name : 'Santri/Warga',
                    $disc->discharge_type,
                    $disc->discharge_destination,
                    $disc->activity_recommendation,
                    $disc->follow_up_required ? 'Ya' : 'Tidak',
                    $disc->follow_up_date ? Carbon::parse($disc->follow_up_date)->format('d/m/Y') : '-',
                    $disc->status,
                ]);
            }
        });
    }

    /**
     * @param  resource  $handle
     * @param  array<string, mixed>  $filters
     */
    protected function streamPharmacyStockReport($handle, array $filters): void
    {
        $this->writeCsvRow($handle, ['Nama Obat', 'Kode', 'Nomor Batch', 'Lokasi', 'Sisa Stok', 'Tanggal Kedaluwarsa', 'Status Kedaluwarsa']);

        $query = MedicineBatch::with(['medicine', 'location'])->orderBy('expiry_date');
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('medicine', function ($mq) use ($search) {
                        $mq->where('brand_name', 'like', "%{$search}%")
                            ->orWhere('generic_name', 'like', "%{$search}%");
                    });
            });
        }

        $query->chunk(100, function ($batches) use ($handle) {
            foreach ($batches as $batch) {
                $isExpired = $batch->expiry_date ? $batch->expiry_date->isPast() : false;
                /** @var Medicine|null $medicine */
                $medicine = $batch->medicine;
                /** @var StockLocation|null $location */
                $location = $batch->location;

                $this->writeCsvRow($handle, [
                    $medicine ? ($medicine->brand_name ?? $medicine->generic_name) : 'Obat',
                    $medicine ? ($medicine->code ?? '-') : '-',
                    $batch->batch_number,
                    $location ? $location->name : 'Apotek Utama',
                    $batch->current_quantity,
                    $batch->expiry_date ? $batch->expiry_date->format('d/m/Y') : '-',
                    $isExpired ? 'Kedaluwarsa' : 'Aktif',
                ]);
            }
        });
    }

    /**
     * @param  resource  $handle
     * @param  array<string, mixed>  $filters
     */
    protected function streamIntegrationDeliveryReport($handle, array $filters): void
    {
        $this->writeCsvRow($handle, ['ID Pengiriman', 'Event ID', 'Destinasi / Modul', 'Percobaan Ke', 'Hasil / Status', 'Kode HTTP', 'Latensi (ms)', 'Error', 'Waktu Mulai']);

        $query = IntegrationDeliveryAttempt::latest('created_at');
        $this->applyDateFilters($query, $filters);
        if (! empty($filters['status'])) {
            $query->where('result', $filters['status']);
        }

        $query->chunk(100, function ($attempts) use ($handle) {
            foreach ($attempts as $att) {
                $this->writeCsvRow($handle, [
                    $att->id,
                    $att->outbox_event_id,
                    $att->destination,
                    $att->attempt_number,
                    $att->result,
                    $att->http_status_code ?? '-',
                    $att->latency_ms ?? '-',
                    $att->sanitized_error ?? '-',
                    $att->started_at->format('d/m/Y H:i:s'),
                ]);
            }
        });
    }

    /**
     * Sanitize individual CSV cell to prevent formula injection (=, +, -, @, \t, \r).
     */
    protected function sanitizeCsvValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        // If string starts with formula triggers, prepend single quote to neutralize
        if (preg_match('/^[\=\+\-\@\t\r]/', $value)) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Write sanitized array as CSV line.
     *
     * @param  resource  $handle
     * @param  array<int, mixed>  $row
     */
    protected function writeCsvRow($handle, array $row): void
    {
        $sanitized = array_map([$this, 'sanitizeCsvValue'], $row);
        fputcsv($handle, $sanitized);
    }

    /**
     * Apply date range filtering to query builder.
     *
     * @param  mixed  $query
     * @param  array<string, mixed>  $filters
     */
    protected function applyDateFilters($query, array $filters): void
    {
        if (! empty($filters['start_date'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['start_date'])->startOfDay());
        }
        if (! empty($filters['end_date'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }
    }
}
