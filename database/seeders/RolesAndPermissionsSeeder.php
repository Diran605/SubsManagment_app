<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates every permission the app checks, plus the default Admin and Staff
 * roles. Safe to run more than once: `php artisan db:seed --class=RolesAndPermissionsSeeder`.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Must match the permission checks in routes/web.php, the controllers and
     * resources/js/pages/settings/Roles/Index.vue.
     */
    public const PERMISSIONS = [
        'view businesses', 'create businesses', 'edit businesses', 'delete businesses',
        'view services', 'create services', 'edit services', 'delete services',
        'create packages', 'edit packages', 'delete packages',
        'create modules', 'edit modules', 'delete modules',
        'view subscriptions', 'create subscriptions', 'edit subscriptions', 'delete subscriptions', 'restore subscriptions',
        'view reports',
        'manage staff', 'manage roles', 'manage settings',
    ];

    /**
     * Default permissions for the Staff role: day-to-day work, no deleting
     * and no administration.
     */
    public const STAFF_PERMISSIONS = [
        'view businesses', 'create businesses', 'edit businesses',
        'view services',
        'view subscriptions', 'create subscriptions', 'edit subscriptions',
        'view reports',
    ];

    /**
     * Older generic permissions that have been replaced by the ones above.
     */
    private const OBSOLETE_PERMISSIONS = ['manage businesses', 'manage services', 'manage subscriptions'];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        Permission::whereIn('name', self::OBSOLETE_PERMISSIONS)->delete();

        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'])
            ->syncPermissions(Permission::all());

        $staff = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);

        // Only give defaults to a new Staff role; don't undo an admin's changes.
        if ($staff->wasRecentlyCreated || $staff->permissions()->doesntExist()) {
            $staff->syncPermissions(self::STAFF_PERMISSIONS);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
