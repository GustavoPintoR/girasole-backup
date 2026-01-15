<?php

namespace Database\Seeders;

use App\Models\CadastralUnit;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;

class CadastralUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CadastralUnit::firstOrCreate([
            'user_id' => User::first()->id,
            'city_id' => City::first()->id,
            'sheet' => 1,
            'parcel' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
