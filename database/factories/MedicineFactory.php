<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medicine>
 */
class MedicineFactory extends Factory
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
            'created_by_id' => User::factory()->doctor(),
            'name' => fake()->randomElement([
                'Aspirina 100mg',
                'Metformina 500mg',
                'Ramipril 5mg',
                'Tachipirina 1000mg',
            ]),
            'description' => fake()->sentence(8),
            'image_url' => 'https://via.placeholder.com/120x120.png?text=Pill',
            'remaining_quantity' => fake()->numberBetween(5, 120),
            'minimum_temperature' => 15,
            'maximum_temperature' => 25,
            'minimum_humidity' => 35,
            'maximum_humidity' => 60,
            'reorder_threshold' => fake()->numberBetween(5, 20),
        ];
    }
}
