<?php

namespace Database\Factories;

use App\Models\Sensor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SensorFactory extends Factory
{
    protected $model = Sensor::class;

    public function definition(): array
    {
        return [
            'serial' => $this->faker->unique()->numberBetween(1, 10000),
            'description' => $this->faker->sentence(),
            'latitude' => $this->faker->latitude(-33, 5),
            'longitude' => $this->faker->longitude(-74, -34),
            'owner_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
        ];
    }

}
