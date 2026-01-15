<?php

namespace Database\Seeders;

use App\Models\SensorField;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SensorFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sensorFields = [
            [
                'name' => 'Massimo',
                'label' => 'X',
            ],
            [
                'name' => 'Minimo',
                'label' => 'N',
            ],
            [
                'name' => 'Media',
                'label' => 'M',
            ],
            [
                'name' => 'Mediana',
                'label' => 'A',
            ],
            [
                'name' => 'UTC time',
                'label' => 'UTC',
            ],
            [
                'name' => 'Tenzione batteria',
                'label' => 'V',
            ],
            [
                'name' => 'Temperatura batteria',
                'label' => 'TC',
            ],
            [
                'name' => 'Corrente trasmissione',
                'label' => 'I',
            ],
            [
                'name' => 'Corrente misura (media)',
                'label' => 'IM',
            ],
            [
                'name' => 'Carica batteria',
                'label' => 'CH',
            ],
            [
                'name' => 'Latitude',
                'label' => 'Lat',
            ],
            [
                'name' => 'Longitude',
                'label' => 'Lon',
            ]
        ];

        foreach ($sensorFields as $sensorField) {
            SensorField::updateOrCreate([
                'name' => $sensorField['name'],
            ], [
                'label' => $sensorField['label'],
            ]);
        }
    }
}
