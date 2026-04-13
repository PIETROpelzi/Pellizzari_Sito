<?php

namespace Database\Factories;

use App\Models\Dispenser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Dispenser>
 */
class DispenserFactory extends Factory
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
            'name' => 'Dispenser '.fake()->numberBetween(1, 999),
            'device_uid' => 'ESP32-'.strtoupper(Str::random(10)),
            'api_token' => Str::random(40),
            'mqtt_base_topic' => 'smart-dispenser/'.strtolower(Str::random(8)),
            'is_active' => true,
            'is_online' => fake()->boolean(70),
            'last_seen_at' => now()->subMinutes(fake()->numberBetween(1, 180)),
        ];
    }
}
