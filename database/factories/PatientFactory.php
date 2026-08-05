<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'person_id' => Person::factory(),
            'patient_number' => Patient::generatePatientNumber(),
            'is_eligible' => true,
            'ineligibility_reason' => null,
            'blood_type' => fake()->randomElement(['A+', 'B+', 'O+', 'AB+']),
            'allergies_summary' => 'Tidak ada riwayat alergi obat yang diketahui.',
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->phoneNumber(),
            'emergency_contact_relation' => 'Orang Tua / Wali',
        ];
    }
}
