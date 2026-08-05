<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Person>
 */
class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'gate_user_id' => 'GATE-'.strtoupper(Str::random(8)),
            'name' => fake()->name(),
            'nik' => fake()->unique()->numerify('3201##############'),
            'nis_nip' => fake()->unique()->numerify('SAN-2026-####'),
            'user_type' => 'santri',
            'gender' => fake()->randomElement(['L', 'P']),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'source_status' => 'active',
            'source_updated_at' => now(),
            'source_version' => 'v1.0',
            'checksum' => md5(fake()->uuid()),
            'synced_at' => now(),
        ];
    }
}
