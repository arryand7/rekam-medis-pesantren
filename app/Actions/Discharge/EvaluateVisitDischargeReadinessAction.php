<?php

namespace App\Actions\Discharge;

use App\Models\ClinicalAssessment;
use App\Models\MedicalVisit;
use App\Models\Referral;

/**
 * Evaluates the technical and domain prerequisites for medical visit discharge.
 *
 * NOTE: This action only checks technical safety invariants and administrative blockers.
 * It NEVER makes clinical decisions or generates automated medical readiness scores.
 */
class EvaluateVisitDischargeReadinessAction
{
    /**
     * Evaluate readiness of a visit for clinical discharge.
     *
     * @return array{
     *     is_ready: bool,
     *     technical_blockers: list<string>,
     *     warnings: list<string>,
     *     visit_state: string
     * }
     */
    public function execute(MedicalVisit $visit): array
    {
        $blockers = [];
        $warnings = [];

        // 1. Visit Status Check
        if ($visit->status === 'cancelled') {
            $blockers[] = 'Kunjungan telah dibatalkan dan tidak dapat dilakukan proses kepulangan (discharge).';
        }

        if ($visit->status === 'discharged') {
            $blockers[] = 'Kunjungan ini sudah berstatus kepulangan final (discharged).';
        }

        // 2. Finalized Clinical Assessment Check
        /** @var ClinicalAssessment|null $assessment */
        $assessment = $visit->assessments()->latest()->first();
        if (! $assessment) {
            $blockers[] = 'Pengkajian klinis (clinical assessment) belum dibuat untuk kunjungan ini.';
        } elseif ($assessment->status !== 'finalized' && $assessment->status !== 'amended') {
            $blockers[] = 'Pengkajian klinis masih berstatus draf. Finalisasi pengkajian klinis terlebih dahulu.';
        }

        // 3. Active Observation Episode Check
        $activeObservations = $visit->observationEpisodes()
            ->whereIn('status', ['planned', 'active'])
            ->count();

        if ($activeObservations > 0) {
            $blockers[] = 'Masih terdapat episode observasi yang aktif. Selesaikan atau batalkan episode observasi terlebih dahulu.';
        }

        // 4. In-Flight Referral Check
        $inFlightReferrals = Referral::where('medical_visit_id', $visit->id)
            ->whereIn('status', [
                'prepared',
                'approved',
                'ready_to_depart',
                'departed',
                'arrived',
                'accepted',
                'under_external_care',
                'return_planned',
            ])
            ->count();

        if ($inFlightReferrals > 0) {
            $blockers[] = 'Terdapat rujukan eksternal yang masih aktif atau dalam proses. Selesaikan alur rujukan sebelum menutup kunjungan.';
        }

        // 5. Returned Referral without Review Check
        $unreviewedReturns = Referral::where('medical_visit_id', $visit->id)
            ->where('status', 'returned')
            ->whereDoesntHave('referralReturn.review')
            ->count();

        if ($unreviewedReturns > 0) {
            $blockers[] = 'Terdapat rujukan yang telah kembali dari faskes tujuan namun belum dilakukan tinjauan klinis lokal (return review).';
        }

        // 6. Active Medication Orders Warning (No automatic discontinuation)
        $activeMedOrders = $visit->medicationOrders()
            ->where('status', 'active')
            ->count();

        if ($activeMedOrders > 0) {
            $warnings[] = "Terdapat {$activeMedOrders} instruksi obat (medication order) yang masih aktif. Pastikan rencana konsumsi obat pasca-pulang telah dijelaskan kepada pasien.";
        }

        // 7. Vital Signs Notice
        if ($visit->vitalSigns()->count() === 0) {
            $warnings[] = 'Belum ada pencatatan tanda vital untuk kunjungan ini.';
        }

        return [
            'is_ready' => count($blockers) === 0,
            'technical_blockers' => $blockers,
            'warnings' => $warnings,
            'visit_state' => $visit->status,
        ];
    }
}
