<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Service;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $threshold = (int) config('subscription.expiring_threshold', 30);

        // Every logged-in user can open the dashboard, but only users allowed to
        // see subscriptions get the revenue figures and client names.
        if (! $request->user()->can('view subscriptions')) {
            return Inertia::render('Dashboard', [
                'canViewSubscriptions' => false,
                'expiringSubscriptions' => [],
                'expiringThreshold' => $threshold,
                'totalRevenue' => 0,
                'subscriptionsByService' => [],
                'subscriptionsByBusiness' => [],
                'totalBusinesses' => 0,
                'totalServices' => 0,
            ]);
        }

        $activeSubscriptions = Subscription::currentlyActive()
            ->with(['business:id,name', 'service:id,name'])
            ->get(['id', 'business_id', 'service_id', 'amount', 'end_date']);

        $subscriptionsByService = $activeSubscriptions
            ->groupBy(fn ($sub) => $sub->service->name ?? 'Unknown')
            ->map(fn ($group) => $group->count());

        $subscriptionsByBusiness = $activeSubscriptions
            ->groupBy(fn ($sub) => $sub->business->name ?? 'Unknown')
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->take(15); // Keep the chart readable

        $expiringSubscriptions = Subscription::whereStatus('expiring_soon')
            ->currentlyActive()
            ->with(['business:id,name', 'service:id,name'])
            ->orderBy('end_date')
            ->get();

        return Inertia::render('Dashboard', [
            'canViewSubscriptions' => true,
            'expiringSubscriptions' => $expiringSubscriptions,
            'expiringThreshold' => $threshold,
            'totalRevenue' => $activeSubscriptions->sum('amount'),
            'subscriptionsByService' => $subscriptionsByService,
            'subscriptionsByBusiness' => $subscriptionsByBusiness,
            'totalBusinesses' => Business::count(),
            'totalServices' => Service::count(),
        ]);
    }
}
