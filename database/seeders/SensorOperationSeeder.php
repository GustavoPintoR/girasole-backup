<?php

namespace Database\Seeders;

use App\Models\SensorOperation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SensorOperationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sensorOperations = [
            [
                'name' => 'T01',
                'label' => 'T01',
            ],
            [
                'name' => 'T02',
                'label' => 'T02',
            ],
            [
                'name' => 'T03',
                'label' => 'T03',
            ],
            [
                'name' => 'T04',
                'label' => 'T04',
            ],
            [
                'name' => 'T05',
                'label' => 'T05',
            ],
            [
                'name' => 'T06',
                'label' => 'T06',
            ],
            [
                'name' => 'T07',
                'label' => 'T07',
            ],
            [
                'name' => 'T08',
                'label' => 'T08',
            ],
            [
                'name' => 'T09',
                'label' => 'T09',
            ],
            [
                'name' => 'T10',
                'label' => 'T10',
            ],
            [
                'name' => 'T11',
                'label' => 'T11',
            ],
            [
                'name' => 'T12',
                'label' => 'T12',
            ],
            [
                'name' => 'T13',
                'label' => 'T13',
            ],
            [
                'name' => 'T14',
                'label' => 'T14',
            ],
            [
                'name' => 'T15',
                'label' => 'T15',
            ],
            [
                'name' => 'T16',
                'label' => 'T16',
            ],
            [
                'name' => 'U01',
                'label' => 'U01',
            ],
            [
                'name' => 'U02',
                'label' => 'U02',
            ],
            [
                'name' => 'U03',
                'label' => 'U03',
            ],
            [
                'name' => 'U04',
                'label' => 'U04',
            ],
            [
                'name' => 'U05',
                'label' => 'U05',
            ],
            [
                'name' => 'U06',
                'label' => 'U06',
            ],
            [
                'name' => 'U07',
                'label' => 'U07',
            ],
            [
                'name' => 'U08',
                'label' => 'U08',
            ],
            [
                'name' => 'U09',
                'label' => 'U09',
            ],
            [
                'name' => 'U10',
                'label' => 'U10',
            ],
            [
                'name' => 'U11',
                'label' => 'U11',
            ],
            [
                'name' => 'U12',
                'label' => 'U12',
            ],
            [
                'name' => 'U13',
                'label' => 'U13',
            ],
            [
                'name' => 'U14',
                'label' => 'U14',
            ],
            [
                'name' => 'U15',
                'label' => 'U15',
            ],
            [
                'name' => 'U16',
                'label' => 'U16',
            ],
        ];

        foreach ($sensorOperations as $sensorOperation) {
            SensorOperation::updateOrCreate([
                'name' => $sensorOperation['name'],
            ], [
                'label' => $sensorOperation['label'],
            ]);
        }
    }
}
