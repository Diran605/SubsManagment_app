<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return Inertia::render('settings/Roles/Index', [
            'roles' => Role::with('permissions')->get(),
            'permissions' => Permission::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $validated['name']]);
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return back()->with('success', 'Role created successfully.');
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Routes check for the "Admin" role by name, and admins must keep full
        // access, so the Admin role cannot be renamed or have permissions removed.
        if ($role->name === 'Admin') {
            if ($validated['name'] !== 'Admin') {
                return back()->withErrors(['name' => 'The Admin role cannot be renamed.']);
            }

            $role->syncPermissions(Permission::all());

            return back()->with('success', 'The Admin role always keeps every permission.');
        }

        $role->update(['name' => $validated['name']]);
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Admin') {
            return back()->withErrors(['name' => 'Cannot delete the Admin role.']);
        }

        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }
}
