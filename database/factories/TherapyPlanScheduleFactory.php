<?php

namespace Database\Factories;

use App\Models\TherapyPlan;
use App\Models\TherapyPlanSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TherapyPlanSchedule>
 */
class TherapyPlanScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'therapy_plan_id' => TherapyPlan::factory(),
            'scheduled_time' => fake()->randomElement(['08:00', '13:00', '20:00']),
            'week_days' => [1, 2, 3, 4, 5, 6, 7],
            'timezone' => 'Europe/Rome',
        ];
    }
}
