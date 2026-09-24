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
        $businesses = Business::withTrashed()->with('creator')->latest()->take(50)->get();
        $bLogs = collect();
        foreach($businesses as $b) {
            $bLogs->push([
                'id' => 'bus_'.$b->id.'_c',
                'action' => 'Created Business: ' . $b->name,
                'user' => $b->creator->name ?? 'System',
                'created_at' => $b->created_at,
                'type' => 'business',
            ]);
            if ($b->trashed()) {
                $bLogs->push([
                    'id' => 'bus_'.$b->id.'_d',
                    'action' => 'Deleted Business: ' . $b->name,
                    'user' => 'System',
                    'created_at' => $b->deleted_at,
                    'type' => 'business',
                ]);
            }
        }

        $services = Service::withTrashed()->with('creator')->latest()->take(50)->get();
        $sLogs = collect();
        foreach($services as $s) {
            $sLogs->push([
                'id' => 'srv_'.$s->id.'_c',
                'action' => 'Created Service: ' . $s->name,
                'user' => $s->creator->name ?? 'System',
                'created_at' => $s->created_at,
                'type' => 'service',
            ]);
            if ($s->trashed()) {
                $sLogs->push([
                    'id' => 'srv_'.$s->id.'_d',
                    'action' => 'Deleted Service: ' . $s->name,
                    'user' => 'System',
                    'created_at' => $s->deleted_at,
                    'type' => 'service',
                ]);
            }
        }

        $subscriptions = Subscription::withTrashed()->with(['creator', 'business', 'service'])->latest()->take(50)->get();
        $subLogs = collect();
        foreach($subscriptions as $sub) {
            $bName = $sub->business->name ?? 'Unknown';
            $sName = $sub->service->name ?? 'Unknown';
            $subLogs->push([
                'id' => 'sub_'.$sub->id.'_c',
                'action' => $sub->renewed_from_id ? "Renewed Subscription for $bName ($sName)" : "Created Subscription for $bName ($sName)",
                'user' => $sub->creator->name ?? 'System',
                'created_at' => $sub->created_at,
                'type' => 'subscription',
            ]);
            if ($sub->trashed()) {
                $subLogs->push([
                    'id' => 'sub_'.$sub->id.'_d',
                    'action' => "Deleted Subscription for $bName ($sName)",
                    'user' => 'System',
                    'created_at' => $sub->deleted_at,
                    'type' => 'subscription',
                ]);
            }
        }

        $logs = $bLogs->concat($sLogs)->concat($subLogs)
            ->sortByDesc('created_at')
            ->values()
            ->take(100);

        return Inertia::render('AuditLogs/Index', [
            'logs' => $logs
        ]);
    }
}
