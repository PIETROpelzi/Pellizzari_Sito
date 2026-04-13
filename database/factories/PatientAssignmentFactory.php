<?php

namespace Database\Factories;

use App\Models\PatientAssignment;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientAssignment>
 */
class PatientAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = fake()->randomElement([UserRole::Doctor->value, UserRole::Caregiver->value]);

        return [
            'patient_id' => User::factory()->patient(),
            'member_id' => $role === UserRole::Doctor->value
                ? User::factory()->doctor()
                : User::factory()->caregiver(),
            'assigned_by_id' => User::factory()->admin(),
            'role' => $role,
        ];
    }
}
