<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        Event::factory()->count(10)->create();

        $testUser = User::where('email', 'test@example.com')->first();
        Event::factory()->count(5)->create(['user_id' => $testUser?->id]);

    }
}
