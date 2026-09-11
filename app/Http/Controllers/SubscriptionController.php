<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Service;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $allSubscriptions = Subscription::with(['business', 'service', 'creator', 'renewals'])->get()->map(function ($sub) {
            if ($sub->renewals->isNotEmpty()) {
                $sub->status_badge = 'renewed';
            } else {
                $sub->status_badge = $sub->status;
            }
            return $sub;
        });

        $filtered = $allSubscriptions
            ->when($search, function($collection) use ($search) {
                return $collection->filter(function($sub) use ($search) {
                    return stripos($sub->business->name ?? '', $search) !== false;
                });
            })
            ->when($statusFilter && $statusFilter !== 'all', function($collection) use ($statusFilter) {
                return $collection->filter(fn ($sub) => $sub->status_badge === $statusFilter);
            })
            ->when($dateFrom, function($collection) use ($dateFrom) {
                $from = \Carbon\Carbon::parse($dateFrom)->startOfDay();
                return $collection->filter(fn ($sub) => \Carbon\Carbon::parse($sub->start_date)->startOfDay()->gte($from));
            })
            ->when($dateTo, function($collection) use ($dateTo) {
                $to = \Carbon\Carbon::parse($dateTo)->endOfDay();
                return $collection->filter(fn ($sub) => \Carbon\Carbon::parse($sub->start_date)->startOfDay()->lte($to));
            })
            ->sortByDesc('created_at')
            ->values();
            
        $page = $request->input('page', 1);
        $perPage = 10;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Subscriptions/Index', [
            'subscriptions' => $paginated,
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function create(Request $request)
    {
        $businessId = $request->input('business_id');

        return Inertia::render('Subscriptions/Form', [
            'businesses' => Business::orderBy('name')->get(['id', 'name']),
            'services' => Service::with(['pricing', 'modules', 'packages.modules'])->orderBy('name')->get(),
            'defaultBusinessId' => $businessId ? (int) $businessId : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'service_id' => 'required|exists:services,id',
            'mode' => 'required|in:online,offline,hybrid',
            'amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'package_id' => 'nullable|exists:packages,id',
            'modules' => 'nullable|array',
            'modules.*' => 'exists:modules,id',
        ]);

        $subscriptionData = $validated;
        unset($subscriptionData['modules']);
        $subscriptionData['created_by'] = $request->user()->id;

        $subscription = Subscription::create($subscriptionData);

        if (isset($validated['modules'])) {
            $subscription->modules()->sync($validated['modules']);
        }

        return redirect()->route('subscriptions.index')->with('success', 'Subscription recorded successfully.');
    }

    public function renewForm(Subscription $subscription)
    {
        $subscription->load(['business', 'service.modules', 'service.packages.modules', 'modules']);

        return Inertia::render('Subscriptions/Form', [
            'businesses' => Business::orderBy('name')->get(['id', 'name']),
            'services' => Service::with(['pricing', 'modules', 'packages.modules'])->orderBy('name')->get(),
            'renewing' => $subscription,
        ]);
    }

    public function renew(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'package_id' => 'nullable|exists:packages,id',
            'modules' => 'nullable|array',
            'modules.*' => 'exists:modules,id',
        ]);

        $newSub = Subscription::create([
            'business_id' => $subscription->business_id,
            'service_id' => $subscription->service_id,
            'mode' => $subscription->mode,
            'amount' => $validated['amount'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'notes' => $validated['notes'] ?? null,
            'package_id' => $validated['package_id'] ?? null,
            'renewed_from_id' => $subscription->id,
            'created_by' => $request->user()->id,
        ]);

        if (isset($validated['modules'])) {
            $newSub->modules()->sync($validated['modules']);
        }

        return redirect()->route('subscriptions.index')->with('success', 'Subscription renewed successfully.');
    }

    public function edit(Subscription $subscription)
    {
        $subscription->load('modules');
        
        return Inertia::render('Subscriptions/Form', [
            'businesses' => Business::orderBy('name')->get(['id', 'name']),
            'services' => Service::with(['pricing', 'modules', 'packages.modules'])->orderBy('name')->get(),
            'subscription' => $subscription,
        ]);
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'service_id' => 'required|exists:services,id',
            'mode' => 'required|in:online,offline,hybrid',
            'amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'package_id' => 'nullable|exists:packages,id',
            'modules' => 'nullable|array',
            'modules.*' => 'exists:modules,id',
        ]);

        $subscriptionData = $validated;
        unset($subscriptionData['modules']);
        
        $subscription->update($subscriptionData);

        if (isset($validated['modules'])) {
            $subscription->modules()->sync($validated['modules']);
        } else {
            $subscription->modules()->detach();
        }

        return redirect()->route('subscriptions.index')->with('success', 'Subscription updated successfully.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return redirect()->back()->with('success', 'Subscription deleted successfully.');
    }
}
