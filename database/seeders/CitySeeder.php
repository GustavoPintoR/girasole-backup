<?php

namespace Database\Seeders;

use App\Actions\ImportCitiesAction;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ImportCitiesAction::run();
    }
}
