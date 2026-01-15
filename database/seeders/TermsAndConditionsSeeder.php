<?php

namespace Database\Seeders;

use App\Models\TermsAndConditions;
use Illuminate\Database\Seeder;

class TermsAndConditionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TermsAndConditions::firstOrCreate([
            'version' => '1.0.0',
            'description' => 'This is the first version of the terms and conditions.',
            'summary' => 'Lorem ipsum',
            'is_active' => true,
            'active_at' => now(),
        ]);
    }
}
