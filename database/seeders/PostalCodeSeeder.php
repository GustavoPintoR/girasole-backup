<?php

namespace Database\Seeders;

use App\Actions\ImportPostalCodesAction;
use Illuminate\Database\Seeder;

class PostalCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ImportPostalCodesAction::run();
    }
}
