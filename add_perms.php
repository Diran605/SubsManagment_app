<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

Permission::firstOrCreate(['name' => 'delete subscriptions']);
Permission::firstOrCreate(['name' => 'restore subscriptions']);

$admin = Role::findByName('Admin');
if ($admin) {
    $admin->givePermissionTo(['delete subscriptions', 'restore subscriptions']);
}

echo "Permissions added successfully!\n";
