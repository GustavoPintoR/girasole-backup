<?php

namespace Database\Factories;

use App\Enums\Plans;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->unique()->slug(),
            'interval' => $this->faker->randomElement(Plans::values()),
            'currency' => 'EUR',
            'unit_amount' => $this->faker->numberBetween(10, 5000), // cents
            'features' => $this->faker->randomElements([
                'Feature 1',
                'Feature 2',
                'Feature 3',
                'Feature 4',
                'Feature 5',
            ], 3),
            'active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }
}
