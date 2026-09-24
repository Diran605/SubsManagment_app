<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

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

    public function index(Request $request)
    {
        $search = $request->input('search');
        $businesses = Business::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })->latest()->paginate(10)->withQueryString();

        return Inertia::render('Businesses/Index', [
            'businesses' => $businesses,
            'filters' => ['search' => $search],
        ]);
    }

    public function create()
    {
        return Inertia::render('Businesses/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = $request->user()->id;
        Business::create($validated);

        return redirect()->route('businesses.index')->with('success', 'Business created successfully.');
    }

    public function show(Business $business)
    {
        $business->load(['subscriptions.service', 'subscriptions.package', 'subscriptions.modules', 'creator']);

        return Inertia::render('Businesses/Show', [
            'business' => $business,
        ]);
    }

    public function edit(Business $business)
    {
        return Inertia::render('Businesses/Form', [
            'business' => $business,
        ]);
    }

    public function update(Request $request, Business $business)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $business->update($validated);

        return redirect()->route('businesses.index')->with('success', 'Business updated successfully.');
    }

    public function destroy(Business $business)
    {
        $business->delete();

        return redirect()->route('businesses.index')->with('success', 'Business deleted successfully.');
    }
}
