<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\PermissionGenerator;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => UserRole::ADMINISTRATOR->value]);
        $technicianRole = Role::firstOrCreate(['name' => UserRole::TECHNICIAN->value]);
        Role::firstOrCreate(['name' => UserRole::AGENT->value]);
        Role::firstOrCreate(['name' => UserRole::SUPER_USER->value]);
        Role::firstOrCreate(['name' => UserRole::INTEGRATION->value]);
        $userRole = Role::firstOrCreate(['name' => UserRole::USER->value]);

        // Create Permissions
        (new PermissionGenerator)->seed();

        $adminRole->syncPermissions(Permission::all());

        $technicianRole->syncPermissions([
            'read_cadastral_group',
            'update_cadastral_group',
        ]);

        $userRole->syncPermissions([
            'read_notification',
            'create_company',
            'read_company',
            'update_company',
            'delete_company',
            'read_cadastral_group',
            'create_cadastral_group',
            'update_cadastral_group',
            'delete_cadastral_group',
            'read_cadastral_unit',
            'create_cadastral_unit',
            'update_cadastral_unit',
            'delete_cadastral_unit'
        ]);

        $adminUser = User::where('email', 'admin@example.com')->first() ?? User::where('email', 'info@soiposervices.com')->first();
        $testUser = User::where('email', 'test@example.com')->first();
        $technicianUser = User::where('email', 'technician@example.com')->first() ?? User::factory()->create([
            'first_name' => 'Technician',
            'last_name' => 'User',
            'email' => 'technician@example.com',
            'is_admin' => false,
            'password' => bcrypt('password'),
            'active' => true,
        ]);

        $technicianUser->assignRole(UserRole::TECHNICIAN->value);
        $adminUser->assignRole(UserRole::ADMINISTRATOR->value);
        $testUser->assignRole(UserRole::USER->value);
    }
}
