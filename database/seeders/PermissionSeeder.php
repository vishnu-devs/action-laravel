<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Product permissions
        Permission::create(['name' => 'products.view']);
        Permission::create(['name' => 'products.create']);
        Permission::create(['name' => 'products.edit']);
        Permission::create(['name' => 'products.delete']);

        // Order permissions
        Permission::create(['name' => 'orders.view']);
        Permission::create(['name' => 'orders.create']);
        Permission::create(['name' => 'orders.edit']);
        Permission::create(['name' => 'orders.delete']);

        // Vendor request permissions
        Permission::create(['name' => 'vendor-requests.view']);
        Permission::create(['name' => 'vendor-requests.approve']);
        Permission::create(['name' => 'vendor-requests.reject']);

        // User management permissions
        Permission::create(['name' => 'users.view']);
        Permission::create(['name' => 'users.create']);
        Permission::create(['name' => 'users.edit']);
        Permission::create(['name' => 'users.delete']);

        // Role & Permission management
        Permission::create(['name' => 'roles.view']);
        Permission::create(['name' => 'roles.create']);
        Permission::create(['name' => 'roles.edit']);
        Permission::create(['name' => 'roles.delete']);
        Permission::create(['name' => 'permissions.view']);
        Permission::create(['name' => 'permissions.assign']);
    }
}