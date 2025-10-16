<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function index()
    {
        $users = User::all();
        $roles = Role::all();
        $permissions = Permission::all();
        return view('admin.role-permissions', compact('users', 'roles', 'permissions'));
    }

    public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name'
        ]);

        Role::create(['name' => $request->name]);
        return back()->with('success', 'Role created successfully');
    }

    public function createPermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name'
        ]);

        Permission::create(['name' => $request->name]);
        return back()->with('success', 'Permission created successfully');
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->syncRoles([$request->role]);
        return back()->with('success', 'Role assigned successfully');
    }

    public function removeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->removeRole($request->role);
        return back()->with('success', 'Role removed successfully');
    }

    public function assignPermission(Request $request)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role = Role::findByName($request->role);
        $role->syncPermissions($request->permissions);
        return back()->with('success', 'Permissions assigned successfully');
    }

    public function removePermission(Request $request)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
            'permission' => 'required|exists:permissions,name'
        ]);

        $role = Role::findByName($request->role);
        $role->revokePermissionTo($request->permission);
        return back()->with('success', 'Permission removed successfully');
    }
}