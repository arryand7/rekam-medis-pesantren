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
        ];
    }
}
