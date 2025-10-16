<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        $users = \App\Models\User::with('roles')->get();
        return view('admin.roles-permissions.index', compact('roles', 'permissions', 'users'));
    }

    public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name'
        ]);

        Role::create(['name' => $request->name]);

        return redirect()->route('admin.roles-permissions.index')
            ->with('success', 'Role created successfully');
    }

    public function createPermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name'
        ]);

        Permission::create(['name' => $request->name]);

        return redirect()->route('admin.roles-permissions.index')
            ->with('success', 'Permission created successfully');
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name'
        ]);

        $user = \App\Models\User::find($request->user_id);
        $user->assignRole($request->role);

        return redirect()->back()
            ->with('success', 'Role assigned successfully');
    }

    public function removeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name'
        ]);

        $user = \App\Models\User::find($request->user_id);
        $user->removeRole($request->role);

        return redirect()->back()
            ->with('success', 'Role removed successfully');
    }

    public function assignPermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission' => 'required|exists:permissions,name'
        ]);

        $role = Role::find($request->role_id);
        $role->givePermissionTo($request->permission);

        return redirect()->route('admin.roles-permissions.index')
            ->with('success', 'Permission assigned successfully');
    }

    public function removePermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission' => 'required|exists:permissions,name'
        ]);

        $role = Role::find($request->role_id);
        $role->revokePermissionTo($request->permission);

        return redirect()->route('admin.roles-permissions.index')
            ->with('success', 'Permission removed successfully');
    }
}