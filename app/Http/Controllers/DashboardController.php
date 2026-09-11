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
        $search = $request->input('search');
        $statusFilter = $request->input('status');

        // All subscriptions for metrics and widgets (unpaginated)
        $allSubscriptions = Subscription::with(['business', 'service', 'creator', 'renewals'])->get()->map(function ($sub) {
            if ($sub->renewals->isNotEmpty()) {
                $sub->status_badge = 'renewed';
            } else {
                $sub->status_badge = $sub->status;
            }
            return $sub;
        });

        // Computed metrics
        $activeRevenue = $allSubscriptions->whereIn('status_badge', ['active', 'expiring_soon'])->sum('amount');
        
        $revenueByMode = $allSubscriptions
            ->whereIn('status_badge', ['active', 'expiring_soon'])
            ->groupBy('mode')
            ->map(fn ($group) => $group->sum('amount'));

        $expiringSubscriptions = $allSubscriptions
            ->where('status_badge', 'expiring_soon')
            ->sortBy('end_date')
            ->values();

        // Paginated subscriptions for the table
        $query = Subscription::with(['business', 'service', 'creator'])
            ->when($search, function ($q, $search) {
                $q->whereHas('business', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            });

        // Note: Filtering by computed status is tricky in SQL, but we can do a naive date filter for basic status filtering on the paginator,
        // or since it's a small app, we can filter the collection and then paginate manually. 
        // For simplicity and to ensure pagination works flawlessly with computed attributes:
        $filtered = $allSubscriptions
            ->when($search, function($collection) use ($search) {
                return $collection->filter(function($sub) use ($search) {
                    return stripos($sub->business->name ?? '', $search) !== false;
                });
            })
            ->when($statusFilter && $statusFilter !== 'all', function($collection) use ($statusFilter) {
                return $collection->filter(fn ($sub) => $sub->status_badge === $statusFilter);
            })
            ->sortByDesc('created_at')
            ->values();
            
        // Manual pagination of the collection
        $page = $request->input('page', 1);
        $perPage = 5;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Dashboard', [
            'subscriptions' => $paginated,
            'expiringSubscriptions' => $expiringSubscriptions,
            'totalRevenue' => $activeRevenue,
            'totalBusinesses' => Business::count(),
            'totalServices' => Service::count(),
            'revenueByMode' => $revenueByMode,
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
            ],
        ]);
    }
}
