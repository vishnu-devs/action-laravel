<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure super admin role exists
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // Assign all permissions to super admin role
        $role->syncPermissions(Permission::all());

        // Ensure super admin user exists
        $user = User::firstOrCreate(
            ['email' => 'admin@b2b.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Assign super admin role to user
        if (! $user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
        }
    }
}