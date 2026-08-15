<?php

namespace App\Queries\MedicalVisit;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class PatientSearchQuery
{
    /**
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     patient_number: string,
     *     nis_nip: string|null,
     *     user_type: string,
     *     label: string
     * }>
     */
    public function search(string $term, int $limit = 20): array
    {
        $term = trim($term);

        if (mb_strlen($term) < 2) {
            return [];
        }

        return DB::table('patients')
            ->join('people', 'people.id', '=', 'patients.person_id')
            ->select([
                'patients.id',
                'patients.patient_number',
                'people.name',
                'people.nis_nip',
                'people.user_type',
            ])
            ->where('patients.is_eligible', true)
            ->where(function (Builder $query) use ($term): void {
                $pattern = '%'.$term.'%';

                $query->where('patients.patient_number', 'like', $pattern)
                    ->orWhere('people.name', 'like', $pattern)
                    ->orWhere('people.nis_nip', 'like', $pattern);
            })
            ->orderBy('people.name')
            ->limit(min(max($limit, 1), 20))
            ->get()
            ->map(function (object $patient): array {
                $id = (string) $patient->id;
                $name = (string) $patient->name;
                $patientNumber = (string) $patient->patient_number;
                $nisNip = is_string($patient->nis_nip) ? $patient->nis_nip : null;
                $userType = (string) $patient->user_type;
                $type = ucfirst(str_replace('_', ' ', $userType));

                return [
                    'id' => $id,
                    'name' => $name,
                    'patient_number' => $patientNumber,
                    'nis_nip' => $nisNip,
                    'user_type' => $userType,
                    'label' => "{$name} ({$patientNumber}) - {$type}",
                ];
            })
            ->all();
    }
}
