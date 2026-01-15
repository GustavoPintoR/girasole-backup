<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Region>
 */
class RegionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Lombardia', 'Lazio', 'Campania', 'Sicilia', 'Veneto',
                'Piemonte', 'Emilia-Romagna', 'Toscana', 'Puglia', 'Calabria',
                'Sardegna', 'Liguria', 'Marche', 'Abruzzo', 'Friuli-Venezia Giulia',
                'Trentino-Alto Adige', 'Umbria', 'Basilicata', 'Molise', 'Valle d\'Aosta',
            ]),
            'code' => $this->faker->unique()->lexify('???'),
        ];
    }
}
