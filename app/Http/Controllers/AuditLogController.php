<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index()
    {
        // To build a generic audit log without an activity package, we can merge recent creations
        $businesses = Business::with('creator')->latest()->take(50)->get()->map(function($b) {
            return [
                'id' => 'bus_'.$b->id,
                'action' => 'Created Business: ' . $b->name,
                'user' => $b->creator->name ?? 'System',
                'created_at' => $b->created_at,
                'type' => 'business',
            ];
        });

        $services = Service::with('creator')->latest()->take(50)->get()->map(function($s) {
            return [
                'id' => 'srv_'.$s->id,
                'action' => 'Created Service: ' . $s->name,
                'user' => $s->creator->name ?? 'System',
                'created_at' => $s->created_at,
                'type' => 'service',
            ];
        });

        $subscriptions = Subscription::with(['creator', 'business', 'service'])->latest()->take(50)->get()->map(function($sub) {
            $bName = $sub->business->name ?? 'Unknown';
            $sName = $sub->service->name ?? 'Unknown';
            return [
                'id' => 'sub_'.$sub->id,
                'action' => $sub->renewed_from_id ? "Renewed Subscription for $bName ($sName)" : "Created Subscription for $bName ($sName)",
                'user' => $sub->creator->name ?? 'System',
                'created_at' => $sub->created_at,
                'type' => 'subscription',
            ];
        });

        $logs = $businesses->concat($services)->concat($subscriptions)
            ->sortByDesc('created_at')
            ->values()
            ->take(100);

        return Inertia::render('AuditLogs/Index', [
            'logs' => $logs
        ]);
    }
}
