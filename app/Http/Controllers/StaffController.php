<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StaffController extends Controller
{
    public function index()
    {
        return Inertia::render('Staff/Index', [
            'staff' => User::with('roles')->latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Staff/Form', [
            'roles' => \Spatie\Permission\Models\Role::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'boolean',
            'roles' => 'array',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('staff.index')->with('success', 'Staff account created successfully.');
    }

    public function edit(User $staff)
    {
        $staff->load('roles');
        return Inertia::render('Staff/Form', [
            'staff' => $staff,
            'roles' => \Spatie\Permission\Models\Role::all(),
        ]);
    }

    public function update(Request $request, User $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($staff->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
            'roles' => 'array',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $validated['is_active'] ?? true,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $staff->update($data);

        if (isset($validated['roles'])) {
            $staff->syncRoles($validated['roles']);
        }

        return redirect()->route('staff.index')->with('success', 'Staff account updated successfully.');
    }

    public function show(User $staff)
    {
        // Load businesses or subscriptions they created if you want an audit trail
        $staff->load(['businesses', 'services', 'subscriptions']);

        return Inertia::render('Staff/Show', [
            'staff' => $staff,
        ]);
    }

    public function toggleActive(User $staff)
    {
        $staff->update([
            'is_active' => !$staff->is_active,
        ]);

        return redirect()->back()->with('success', 'Staff account status updated.');
    }
}
