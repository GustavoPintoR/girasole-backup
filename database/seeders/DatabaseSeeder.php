<?php

namespace Database\Seeders;

use App\Actions\ImportAllAction;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'is_admin' => false,
            'password' => bcrypt('password'),
            'active' => true,
        ]);

        User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'password' => bcrypt('password'),
            'active' => true,
        ]);

        User::factory()->create([
            'first_name' => 'Technician',
            'last_name' => 'User',
            'email' => 'technician@example.com',
            'is_admin' => false,
            'password' => bcrypt('password'),
            'active' => true,
        ]);

        ImportAllAction::run();

        $this->call([
            RolePermissionSeeder::class,
            SensorTypeSeeder::class,
            SensorOperationSeeder::class,
            SensorFieldSeeder::class,
        ]);
    }
}
