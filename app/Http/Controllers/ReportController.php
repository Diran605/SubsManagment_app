<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        // All subscriptions with their renewals to track history accurately
        $subscriptions = Subscription::with(['business', 'service', 'renewals'])->get();
        
        // Use our computed attribute logic indirectly by filtering, explicitly excluding superseded (renewed) ones
        $activeSubscriptions = $subscriptions->filter(fn ($sub) => 
            $sub->renewals->isEmpty() && in_array($sub->status, ['active', 'expiring_soon'])
        );

        // Revenue by Service
        $revenueByService = $activeSubscriptions
            ->groupBy(fn($sub) => $sub->service->name ?? 'Unknown')
            ->map(fn ($group) => $group->sum('amount'))
            ->sortDesc()
            ->take(10)
            ->toArray();

        // Revenue by Business (Top 10)
        $revenueByBusiness = $activeSubscriptions
            ->groupBy(fn($sub) => $sub->business->name ?? 'Unknown')
            ->map(fn ($group) => $group->sum('amount'))
            ->sortDesc()
            ->take(10)
            ->toArray();

        // Revenue by Mode
        $revenueByMode = $activeSubscriptions
            ->groupBy('mode')
            ->map(fn ($group) => $group->sum('amount'))
            ->toArray();

        // Total historical vs active
        $totalHistoricalRevenue = $subscriptions->sum('amount');
        $totalActiveRevenue = $activeSubscriptions->sum('amount');

        // New vs Renewed split for active subscriptions
        $newRevenue = $activeSubscriptions->whereNull('renewed_from_id')->sum('amount');
        $renewedRevenue = $activeSubscriptions->whereNotNull('renewed_from_id')->sum('amount');

        return Inertia::render('Reports/Index', [
            'metrics' => [
                'totalHistoricalRevenue' => $totalHistoricalRevenue,
                'totalActiveRevenue' => $totalActiveRevenue,
                'newRevenue' => $newRevenue,
                'renewedRevenue' => $renewedRevenue,
            ],
            'charts' => [
                'revenueByService' => $revenueByService,
                'revenueByBusiness' => $revenueByBusiness,
                'revenueByMode' => $revenueByMode,
            ],
        ]);
    }
}
