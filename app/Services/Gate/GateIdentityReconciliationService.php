<?php

namespace App\Services\Gate;

use App\Models\GateIdentityMapping;
use App\Models\GateSyncRun;
use App\Models\Person;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GateIdentityReconciliationService
{
    /**
     * @return array<string, mixed>
     */
    public function getOverview(): array
    {
        $totalMappedPeople = Person::whereNotNull('gate_user_id')->count();
        $totalUnmappedPeople = Person::whereNull('gate_user_id')->count();
        $pendingMappings = GateIdentityMapping::where('status', 'pending')->count();
        $approvedMappings = GateIdentityMapping::where('status', 'approved')->count();
        $recentSyncRuns = GateSyncRun::latest('started_at')->limit(10)->get();

        return [
            'total_mapped' => $totalMappedPeople,
            'total_unmapped' => $totalUnmappedPeople,
            'pending_mappings_count' => $pendingMappings,
            'approved_mappings_count' => $approvedMappings,
            'recent_runs' => $recentSyncRuns,
        ];
    }

    /**
     * Get paginated pending and active identity mappings/conflicts.
     */
    public function getMappings(?string $status = 'pending', int $perPage = 20): LengthAwarePaginator
    {
        $query = GateIdentityMapping::with(['person', 'approvedBy'])
            ->latest('created_at');

        if (! empty($status)) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Approve a legacy/candidate identity mapping.
     */
    public function approveMapping(GateIdentityMapping $mapping, User $actor, ?string $notes = null): GateIdentityMapping
    {
        return DB::transaction(function () use ($mapping, $actor, $notes) {
            $person = Person::findOrFail($mapping->person_id);

            // Assign gate_user_id to person
            $person->update([
                'gate_user_id' => $mapping->gate_user_id,
                'synced_at' => now(),
            ]);

            $mapping->update([
                'status' => 'approved',
                'approved_by_id' => $actor->id,
                'approved_at' => now(),
                'notes' => $notes ?? $mapping->notes,
            ]);

            AuditLogService::log(
                action: 'gate_identity_mapping.approved',
                subjectType: 'GateIdentityMapping',
                subjectId: $mapping->id,
                before: null,
                after: $mapping->toArray(),
                reason: "Pemetaan identitas Gate {$mapping->gate_user_id} ke person {$person->name} disetujui oleh {$actor->name}"
            );

            return $mapping;
        });
    }

    /**
     * Reject a candidate identity mapping.
     */
    public function rejectMapping(GateIdentityMapping $mapping, User $actor, ?string $notes = null): GateIdentityMapping
    {
        $mapping->update([
            'status' => 'rejected',
            'approved_by_id' => $actor->id,
            'approved_at' => now(),
            'notes' => $notes ?? $mapping->notes,
        ]);

        AuditLogService::log(
            action: 'gate_identity_mapping.rejected',
            subjectType: 'GateIdentityMapping',
            subjectId: $mapping->id,
            before: null,
            after: $mapping->toArray(),
            reason: "Pemetaan identitas Gate {$mapping->gate_user_id} ditolak oleh {$actor->name}"
        );

        return $mapping;
    }
}
