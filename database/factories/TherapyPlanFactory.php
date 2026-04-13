<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\TherapyPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TherapyPlan>
 */
class TherapyPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => User::factory()->patient(),
            'doctor_id' => User::factory()->doctor(),
            'medicine_id' => Medicine::factory(),
            'dose_amount' => fake()->randomElement([0.5, 1, 1.5, 2]),
            'dose_unit' => fake()->randomElement(['compressa', 'ml', 'gocce']),
            'instructions' => fake()->sentence(12),
            'starts_on' => now()->subDays(fake()->numberBetween(2, 20))->toDateString(),
            'ends_on' => fake()->boolean(40) ? now()->addDays(fake()->numberBetween(5, 60))->toDateString() : null,
            'is_active' => true,
        ];
    }
}
