<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class BusinessController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view businesses', only: ['index', 'show']),
            new Middleware('permission:create businesses', only: ['create', 'store']),
            new Middleware('permission:edit businesses', only: ['edit', 'update']),
            new Middleware('permission:delete businesses', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $businesses = Business::when($search, function ($query, $search) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%")
                ->orWhere('landmark', 'like', "%{$search}%"));
        })->latest()->paginate(10)->withQueryString();

        return Inertia::render('Businesses/Index', [
            'businesses' => $businesses,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Businesses/Form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = $request->user()->id;
        Business::create($validated);

        return redirect()->route('businesses.index')->with('success', 'Business created successfully.');
    }

    public function show(Business $business): Response
    {
        $business->load([
            'subscriptions' => fn ($query) => $query->withExists('renewals')->latest('start_date'),
            'subscriptions.service:id,name',
            'subscriptions.package:id,name',
            'subscriptions.modules:id,name',
            'creator:id,name',
        ]);
        $business->subscriptions->each(
            fn (Subscription $subscription) => $subscription->setAttribute('status_badge', $subscription->statusBadge())
        );

        return Inertia::render('Businesses/Show', [
            'business' => $business,
        ]);
    }

    public function edit(Business $business): Response
    {
        return Inertia::render('Businesses/Form', [
            'business' => $business,
        ]);
    }

    public function update(Request $request, Business $business): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
        ]);

        $business->update($validated);

        return redirect()->route('businesses.index')->with('success', 'Business updated successfully.');
    }

    public function destroy(Business $business): RedirectResponse
    {
        $business->delete();

        return redirect()->route('businesses.index')->with('success', 'Business deleted successfully.');
    }
}
