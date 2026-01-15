<?php

namespace Database\Factories;

use App\Models\CadastralGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ForecastLog>
 */
class ForecastLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Always use one of the first 10 existing fields
        $fieldId = CadastralGroup::orderBy('id')->limit(10)->inRandomOrder()->first()?->id;

        return [
            'field_id' => $fieldId,
            'status'   => $this->faker->randomElement(['success', 'failed']),
            'data'     => [
                'forecast_date'  => $this->faker->dateTimeBetween('-1 year', '+1 month')->format('Y-m-d'),
                'temperature'    => $this->faker->randomFloat(2, -10, 40),
                'precipitation'  => $this->faker->randomFloat(2, 0, 200),
                'humidity'       => $this->faker->numberBetween(20, 100),
                'wind_speed'     => $this->faker->randomFloat(2, 0, 120),
                'pressure'       => $this->faker->randomFloat(2, 980, 1050),
                'crop_yield_est' => $this->faker->randomFloat(2, 1000, 8000),
                'source'         => $this->faker->randomElement(['NOAA', 'ECMWF', 'OpenWeather', 'Custom Model']),
                'confidence'     => $this->faker->randomFloat(2, 0.65, 0.99),
            ],
        ];
    }
}
