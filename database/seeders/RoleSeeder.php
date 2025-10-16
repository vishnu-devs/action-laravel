<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Rename legacy 'superadmin' role to standardized 'super_admin' if present
        if ($legacy = Role::where('name', 'superadmin')->first()) {
            $legacy->name = 'super_admin';
            $legacy->guard_name = $legacy->guard_name ?: 'web';
            $legacy->save();
        }

        // Create roles (idempotent and standardized)
        $superadminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']);
        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        // Create permissions
        $manageUsers = Permission::create(['name' => 'manage users']);
        $manageRoles = Permission::create(['name' => 'manage roles']);
        $managePermissions = Permission::create(['name' => 'manage permissions']);
        $manageVendors = Permission::create(['name' => 'manage vendors']);
        $manageProducts = Permission::create(['name' => 'manage products']);
        $manageOrders = Permission::create(['name' => 'manage orders']);
        $viewDashboard = Permission::create(['name' => 'view dashboard']);

        // Assign permissions to roles
        $superadminRole->syncPermissions([
            $manageUsers,
            $manageRoles,
            $managePermissions,
            $manageVendors,
            $manageProducts,
            $manageOrders,
            $viewDashboard,
        ]);

        $adminRole->syncPermissions([
            $manageUsers,
            $manageVendors,
            $manageProducts,
            $manageOrders,
            $viewDashboard,
        ]);

        $vendorRole->syncPermissions([
            $manageProducts,
            $manageOrders,
            $viewDashboard,
        ]);

        $customerRole->syncPermissions([
            $manageOrders,
            $viewDashboard,
        ]);
    }
}