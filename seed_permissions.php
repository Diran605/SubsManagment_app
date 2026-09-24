<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$permissions = [
    'view businesses', 'create businesses', 'edit businesses', 'delete businesses',
    'view services', 'create services', 'edit services', 'delete services',
    'create packages', 'edit packages', 'delete packages',
    'create modules', 'edit modules', 'delete modules',
    'view subscriptions', 'create subscriptions', 'edit subscriptions', 'delete subscriptions', 'restore subscriptions',
    'view reports', 'manage staff', 'manage roles', 'manage settings'
];

foreach ($permissions as $p) {
    Permission::firstOrCreate(['name' => $p]);
}

$admin = Role::findByName('Admin');
if ($admin) {
    $admin->givePermissionTo($permissions);
}

// Clean up old generic permissions to avoid clutter in the UI
$old = ['manage businesses', 'manage services', 'manage subscriptions'];
foreach ($old as $o) {
    $perm = Permission::where('name', $o)->first();
    if ($perm) $perm->delete();
}

echo "All granular permissions seeded successfully!\n";
