<?php

namespace Database\Factories;

use App\Models\Dispenser;
use App\Models\DoseLog;
use App\Models\Medicine;
use App\Models\TherapyPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoseLog>
 */
class DoseLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement([
            DoseLog::STATUS_TAKEN,
            DoseLog::STATUS_DISPENSED,
            DoseLog::STATUS_MISSED,
            DoseLog::STATUS_SNOOZED,
        ]);

        return [
            'patient_id' => User::factory()->patient(),
            'dispenser_id' => Dispenser::factory(),
            'therapy_plan_id' => TherapyPlan::factory(),
            'medicine_id' => Medicine::factory(),
            'status' => $status,
            'source' => fake()->randomElement(['Device', 'System', 'Mobile']),
            'scheduled_for' => now()->subHours(fake()->numberBetween(1, 240)),
            'event_at' => now()->subHours(fake()->numberBetween(1, 240)),
            'notes' => fake()->optional()->sentence(6),
        ];
    }
}
