<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        \Spatie\Permission\Models\Permission::create(['name' => 'manage staff']);
        \Spatie\Permission\Models\Permission::create(['name' => 'manage settings']);
        \Spatie\Permission\Models\Permission::create(['name' => 'manage roles']);
        \Spatie\Permission\Models\Permission::create(['name' => 'manage services']);
        \Spatie\Permission\Models\Permission::create(['name' => 'manage businesses']);
        \Spatie\Permission\Models\Permission::create(['name' => 'manage subscriptions']);
        \Spatie\Permission\Models\Permission::create(['name' => 'view reports']);

        // create roles and assign created permissions
        $role1 = \Spatie\Permission\Models\Role::create(['name' => 'Staff']);
        $role1->givePermissionTo(['manage businesses', 'manage subscriptions', 'view reports', 'manage services']);

        $role2 = \Spatie\Permission\Models\Role::create(['name' => 'Admin']);
        $role2->givePermissionTo(\Spatie\Permission\Models\Permission::all());

        // Assign Admin role to the first user if exists
        $user = \App\Models\User::first();
        if ($user) {
            $user->assignRole($role2);
        }
    }
}
