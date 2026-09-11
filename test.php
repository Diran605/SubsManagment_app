<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$all = \App\Models\Subscription::get()->map(function($s) {
    $s->status_badge = $s->status;
    return $s;
});
$res = $all->whereIn('status_badge', ['active', 'expiring_soon'])->groupBy('mode')->map(function ($g) {
    return $g->sum('amount');
});
echo json_encode($res);
