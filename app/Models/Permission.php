<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    public const PROTECTED_PERMISSIONS = [
        'manage-roles',
        'manage-permissions',
        'manage-system-settings',
        'manage-gate-sync',
        'execute-gate-sync-apply',
        'resolve-identity-conflicts',
        'manage-attendance-integration-settings',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions', 'permission_id', 'role_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'model_has_permissions', 'permission_id', 'model_id');
    }

    public function isProtected(): bool
    {
        return in_array($this->name, self::PROTECTED_PERMISSIONS, true);
    }

    public static function isNameProtected(string $name): bool
    {
        return in_array($name, self::PROTECTED_PERMISSIONS, true);
    }

    /**
     * Group permissions logically for administration matrix UI.
     *
     * @return array<string, array{title: string, description: string, permissions: Collection<int, Permission>}>
     */
    public static function getGroupedPermissions(): array
    {
        $all = static::orderBy('name')->get();

        $groups = [
            'dashboard' => [
                'title' => 'Dashboard & Workspaces',
                'description' => 'Akses ke dashboard klinis, farmasi, operasional, dan manajerial',
                'prefixes' => ['view-clinical-dashboard', 'view-pharmacy-dashboard', 'view-operational-dashboard', 'view-management-dashboard'],
                'items' => collect(),
            ],
            'patient_visit' => [
                'title' => 'Pasien & Pendaftaran (Intake)',
                'description' => 'Akses rekam medis pasien, profil kesehatan, dan registrasi kunjungan',
                'prefixes' => ['view-patients', 'view-patient-health-profile', 'update-patient-health-profile', 'manage-patient-allergies', 'manage-patient-conditions', 'manage-emergency-contacts', 'create-medical-visits', 'view-medical-visits', 'cancel-medical-visits', 'override-active-visit'],
                'items' => collect(),
            ],
            'clinical' => [
                'title' => 'Pelayanan Klinis, Observasi & Rujukan',
                'description' => 'Pemeriksaan vital sign, pengkajian dokter/perawat, observasi, konsultasi eksternal, rujukan faskes, dan pemulangan',
                'prefixes' => [
                    'record-vital-signs', 'finalize-vital-signs',
                    'create-clinical-assessments', 'finalize-clinical-assessments', 'amend-clinical-assessments', 'record-working-diagnosis', 'record-initial-actions', 'recommend-visit-disposition',
                    'start-observations', 'view-observations', 'record-observation-monitoring', 'finalize-observation-monitoring', 'amend-observation-monitoring', 'prepare-observation-handover', 'acknowledge-observation-handover', 'complete-observations', 'cancel-observations', 'view-observation-audit',
                    'view-clinical-consultations', 'create-clinical-consultations', 'finalize-clinical-consultation-summaries', 'send-clinical-consultations', 'cancel-clinical-consultations', 'record-external-clinical-advice', 'verify-external-clinical-advice', 'finalize-local-clinical-decisions', 'download-clinical-consultation-documents', 'view-clinical-consultation-transmissions',
                    'view-referrals', 'create-referrals', 'approve-referrals', 'prepare-referral-documents', 'arrange-referral-transport', 'assign-referral-companions', 'record-referral-departure', 'record-referral-handover', 'record-destination-status', 'record-referral-returns', 'review-referral-returns', 'cancel-referrals', 'download-referral-documents',
                    'view-visit-discharges', 'prepare-visit-discharges', 'finalize-visit-discharges', 'download-discharge-summaries', 'manage-activity-restrictions', 'manage-follow-up-plans', 'view-follow-up-plans',
                ],
                'items' => collect(),
            ],
            'pharmacy' => [
                'title' => 'Farmasi, Obat & Peresepan',
                'description' => 'Inventaris obat, mutasi stok, penerimaan obat, dan dispensing peresepan',
                'prefixes' => [
                    'view-pharmacy-inventory', 'manage-medicine-master', 'manage-medicines', 'receive-medicine-stock', 'adjust-medicine-stock', 'reverse-stock-movements', 'transfer-medicine-stock', 'view-stock-movements', 'view-stock-reconciliation', 'manage-stock-locations',
                    'view-medication-orders', 'create-medication-orders', 'activate-medication-orders', 'revise-medication-orders', 'discontinue-medication-orders', 'view-medication-administrations', 'schedule-medication-administrations', 'administer-medications', 'administer-one-time-medication', 'hold-medications', 'record-medication-refusal', 'record-missed-medication', 'correct-medication-administrations',
                ],
                'items' => collect(),
            ],
            'operational' => [
                'title' => 'Operasional Asrama & Notifikasi',
                'description' => 'Serah terima informasi pembatasan aktivitas santri dan notifikasi operasional',
                'prefixes' => [
                    'view-operational-handoffs', 'prepare-operational-handoffs', 'acknowledge-operational-handoffs',
                    'view-operational-notifications', 'prepare-operational-notifications', 'acknowledge-operational-notifications',
                ],
                'items' => collect(),
            ],
            'reporting' => [
                'title' => 'Laporan & Sensus Kesehatan',
                'description' => 'Melihat dan mengekspor sensus kunjungan, observasi, rujukan, kepulangan, dan stok farmasi',
                'prefixes' => ['view-health-reports', 'export-health-reports', 'view-reports'],
                'items' => collect(),
            ],
            'administration' => [
                'title' => 'Administrasi & Data Induk',
                'description' => 'Pengelolaan akun pengguna, direktori person, dan data mitra faskes',
                'prefixes' => ['view-people', 'manage-users', 'view-healthcare-partners', 'manage-healthcare-partners', 'verify-healthcare-partner-contacts'],
                'items' => collect(),
            ],
            'system' => [
                'title' => 'Sistem & Konfigurasi Tingkat Tinggi (Protected)',
                'description' => 'Hak istimewa pengelolaan hak akses, integrasi Gate, dan audit keamanan',
                'prefixes' => [
                    'manage-roles', 'manage-permissions', 'view-audit-log', 'manage-system-settings',
                    'view-gate-sync', 'manage-gate-sync', 'execute-gate-sync-apply', 'manage-identity-mappings', 'view-gate-reconciliation', 'resolve-identity-conflicts',
                    'view-integration-outbox', 'retry-integration-events', 'resolve-integration-conflicts', 'view-attendance-integration-status', 'manage-attendance-integration-settings',
                ],
                'items' => collect(),
            ],
        ];

        foreach ($all as $perm) {
            $placed = false;
            foreach ($groups as $key => &$group) {
                if (in_array($perm->name, $group['prefixes'], true)) {
                    $group['items']->push($perm);
                    $placed = true;
                    break;
                }
            }
            unset($group);

            if (! $placed) {
                // Default to system group if unmapped
                $groups['system']['items']->push($perm);
            }
        }

        $result = [];
        foreach ($groups as $key => $group) {
            $result[$key] = [
                'title' => $group['title'],
                'description' => $group['description'],
                'permissions' => $group['items'],
            ];
        }

        return $result;
    }
}
