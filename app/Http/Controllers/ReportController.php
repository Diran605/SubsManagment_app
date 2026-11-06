<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', '12m'); // '6m', '12m', 'all'

        $subscriptionsQuery = Subscription::with([
            'business', 'service', 'renewals', 'package:id,payer_type',
            'collections:id,subscription_id,amount,collection_date',
        ]);

        if ($period !== 'all') {
            $months = $period === '6m' ? 6 : 12;
            $subscriptionsQuery->where('start_date', '>=', now()->subMonths($months)->startOfMonth());
        }

        $subscriptions = $subscriptionsQuery->get();

        // Use our computed attribute logic indirectly by filtering, explicitly excluding superseded (renewed) ones
        // Businesses that have been deleted no longer bring in revenue.
        $activeSubscriptions = $subscriptions->filter(fn ($sub) => $sub->renewals->isEmpty()
            && in_array($sub->status, ['active', 'expiring_soon'])
            && ! $sub->business?->trashed()
        );

        // Revenue Over Time (Monthly). School-paid subscriptions count in the
        // month they start; student MoMo money counts in the month it was collected.
        $revenueEvents = $subscriptions->flatMap(fn ($sub) => $sub->isStudentFunded()
            ? $sub->collections->map(fn ($c) => ['date' => $c->collection_date, 'amount' => (float) $c->amount])
            : [['date' => $sub->start_date, 'amount' => (float) $sub->amount]]
        );

        $revenueOverTime = $revenueEvents
            ->groupBy(fn ($event) => Carbon::parse($event['date'])->format('Y-m'))
            ->sortKeys()
            ->mapWithKeys(fn ($group, $key) => [
                Carbon::parse($key.'-01')->format('M Y') => $group->sum('amount'),
            ])
            ->toArray();

        // Revenue by Service
        $revenueByService = $activeSubscriptions
            ->groupBy(fn ($sub) => $sub->service->name ?? 'Unknown')
            ->map(fn ($group) => $group->sum->realisedRevenue())
            ->sortDesc()
            ->take(10)
            ->toArray();

        // Revenue by Business (Top 10)
        $revenueByBusiness = $activeSubscriptions
            ->groupBy(fn ($sub) => $sub->business->name ?? 'Unknown')
            ->map(fn ($group) => $group->sum->realisedRevenue())
            ->sortDesc()
            ->take(10)
            ->toArray();

        // Revenue by Mode
        $revenueByMode = $activeSubscriptions
            ->groupBy('mode')
            ->map(fn ($group) => $group->sum->realisedRevenue())
            ->toArray();

        // Total historical vs active
        $totalHistoricalRevenue = $subscriptions->sum->realisedRevenue();
        $totalActiveRevenue = $activeSubscriptions->sum->realisedRevenue();

        // New vs Renewed split for active subscriptions
        $newCount = $activeSubscriptions->whereNull('renewed_from_id')->count();
        $renewedCount = $activeSubscriptions->whereNotNull('renewed_from_id')->count();

        $renewalRate = ($newCount + $renewedCount) > 0
            ? round(($renewedCount / ($newCount + $renewedCount)) * 100, 1)
            : 0;

        $newRevenue = $activeSubscriptions->whereNull('renewed_from_id')->sum->realisedRevenue();
        $renewedRevenue = $activeSubscriptions->whereNotNull('renewed_from_id')->sum->realisedRevenue();

        return Inertia::render('Reports/Index', [
            'period' => $period,
            'metrics' => [
                'totalHistoricalRevenue' => $totalHistoricalRevenue,
                'totalActiveRevenue' => $totalActiveRevenue,
                'newRevenue' => $newRevenue,
                'renewedRevenue' => $renewedRevenue,
                'renewalRate' => $renewalRate,
            ],
            'charts' => [
                'revenueOverTime' => $revenueOverTime,
                'revenueByService' => $revenueByService,
                'revenueByBusiness' => $revenueByBusiness,
                'revenueByMode' => $revenueByMode,
            ],
        ]);
    }
}
