<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Staff/Index', [
            'staff' => User::with('roles')->latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Staff/Form', [
            'roles' => Role::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Accounts created by an admin are trusted, so skip the email verification step.
        $user->forceFill(['email_verified_at' => now()])->save();

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
            $this->logRoleChange($user, [], $validated['roles']);
        }

        return redirect()->route('staff.index')->with('success', 'Staff account created successfully.');
    }

    public function edit(User $staff): Response
    {
        $staff->load('roles');

        return Inertia::render('Staff/Form', [
            'staff' => $staff,
            'roles' => Role::all(),
        ]);
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($staff->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $isSelf = $request->user()->is($staff);

        if ($isSelf && array_key_exists('is_active', $validated) && ! $validated['is_active']) {
            return back()->withErrors(['is_active' => 'You cannot deactivate your own account.']);
        }

        if ($isSelf && isset($validated['roles']) && ! in_array('Admin', $validated['roles'], true)) {
            return back()->withErrors(['roles' => 'You cannot remove the Admin role from your own account.']);
        }

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
            $oldRoles = $staff->getRoleNames()->all();
            $staff->syncRoles($validated['roles']);
            $this->logRoleChange($staff, $oldRoles, $validated['roles']);
        }

        return redirect()->route('staff.index')->with('success', 'Staff account updated successfully.');
    }

    public function show(User $staff): Response
    {
        $staff->load(['businesses', 'services', 'subscriptions']);

        return Inertia::render('Staff/Show', [
            'staff' => $staff,
        ]);
    }

    public function toggleActive(Request $request, User $staff): RedirectResponse
    {
        if ($request->user()->is($staff)) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $staff->update([
            'is_active' => ! $staff->is_active,
        ]);

        return redirect()->back()->with('success', 'Staff account status updated.');
    }

    /**
     * Role assignments aren't model attributes, so record them by hand.
     *
     * @param  array<int, string>  $old
     * @param  array<int, string>  $new
     */
    private function logRoleChange(User $user, array $old, array $new): void
    {
        sort($old);
        sort($new);

        if ($old === $new) {
            return;
        }

        activity('user')
            ->performedOn($user)
            ->event('roles_changed')
            ->withProperties([
                'old' => ['roles' => $old],
                'attributes' => ['roles' => $new],
                'label' => $user->activityLabel(),
            ])
            ->log('roles_changed');
    }
}
