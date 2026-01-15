<?php

namespace Database\Seeders;

use App\Models\SensorType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SensorTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sensorTypes = [
            [
                'name' => 'Microclima',
                'description' => 'Microclima',
            ],
            [
                'name' => 'Suolo',
                'description' => 'Suolo',
            ]
        ];

        foreach ($sensorTypes as $sensorType) {
            SensorType::updateOrCreate([
                'name' => $sensorType['name'],
            ],[
                'description' => $sensorType['description'],
            ]);
        }
    }
}
