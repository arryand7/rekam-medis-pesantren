<?php

namespace App\Services\Reporting;

use App\Models\IntegrationDeliveryAttempt;
use App\Models\MedicalVisit;
use App\Models\MedicineBatch;
use App\Models\ObservationEpisode;
use App\Models\Referral;
use App\Models\VisitDischarge;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Service providing core clinical census and management reports with filtering and pagination.
 */
class HealthReportService
{
    /**
     * Get paginated visit census report.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getVisitCensus(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MedicalVisit::with(['patient.person', 'receivingOfficer'])
            ->latest('created_at');

        if (! empty($filters['start_date'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['start_date'])->startOfDay());
        }
        if (! empty($filters['end_date'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }
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
        $query = ObservationEpisode::with(['medicalVisit.patient.person', 'admittedBy', 'dischargedBy'])
            ->latest('created_at');

        if (! empty($filters['start_date'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['start_date'])->startOfDay());
        }
        if (! empty($filters['end_date'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get paginated referral census report.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getReferralCensus(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Referral::with(['medicalVisit.patient.person', 'healthcarePartner', 'createdByUser'])
            ->latest('created_at');

        if (! empty($filters['start_date'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['start_date'])->startOfDay());
        }
        if (! empty($filters['end_date'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }
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
        $query = VisitDischarge::with(['medicalVisit.patient.person', 'followUpPlans', 'preparedBy', 'finalizedBy'])
            ->latest('created_at');

        if (! empty($filters['start_date'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['start_date'])->startOfDay());
        }
        if (! empty($filters['end_date'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get paginated pharmacy stock report.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPharmacyStockReport(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MedicineBatch::with(['medicine', 'location'])
            ->orderBy('expiry_date');

        if (! empty($filters['is_low_stock'])) {
            $query->where('current_quantity', '<=', 10);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get paginated integration delivery status report.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getIntegrationDeliveryReport(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = IntegrationDeliveryAttempt::with(['outboxEvent'])
            ->latest('started_at');

        if (! empty($filters['destination'])) {
            $query->where('destination', $filters['destination']);
        }
        if (! empty($filters['result'])) {
            $query->where('result', $filters['result']);
        }

        return $query->paginate($perPage);
    }
}
