<?php

namespace Database\Seeders;

use App\Models\Cultivar;
use Illuminate\Database\Seeder;

class CultivarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cultivar::factory(10)->create();
    }
}
