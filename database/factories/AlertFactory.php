<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\Dispenser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $triggeredAt = now()->subHours(fake()->numberBetween(1, 72));

        return [
            'patient_id' => User::factory()->patient(),
            'dispenser_id' => Dispenser::factory(),
            'type' => fake()->randomElement([
                Alert::TYPE_HUMIDITY,
                Alert::TYPE_TEMPERATURE,
                Alert::TYPE_MISSED_DOSE,
                Alert::TYPE_STOCK_LOW,
            ]),
            'severity' => fake()->randomElement(['Low', 'Medium', 'High']),
            'message' => fake()->sentence(10),
            'triggered_at' => $triggeredAt,
            'resolved_at' => fake()->boolean(30) ? $triggeredAt->copy()->addHours(2) : null,
            'notified_caregiver' => fake()->boolean(60),
            'notified_doctor' => fake()->boolean(60),
        ];
    }
}
