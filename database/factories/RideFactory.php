<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ride>
 */
class RideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'driver_id' => null,
            'pickup_location' => fake()->address(),
            'dropoff_location' => fake()->address(),
            'price' => fake()->numberBetween(5000, 100000),
            'status' => 'pending',
        ];
    }
}
