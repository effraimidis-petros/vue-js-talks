<?php

namespace Database\Factories;

use App\Models\Run;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Run>
 */
class RunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $distanceKm = fake()->randomFloat(2, 3, 21);
        $secondsPerKm = fake()->numberBetween(300, 390);

        return [
            'user_id' => User::factory(),
            'ran_on' => fake()->dateTimeBetween('-1 month', 'now'),
            'distance_km' => $distanceKm,
            'duration_seconds' => (int) round($distanceKm * $secondsPerKm),
        ];
    }
}
