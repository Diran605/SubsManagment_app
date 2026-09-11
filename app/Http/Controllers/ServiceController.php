<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $services = Service::with('pricing')->latest()->get();

        return Inertia::render('Services/Index', [
            'services' => $services,
        ]);
    }

    public function create()
    {
        return Inertia::render('Services/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prices' => 'array',
            'prices.*.mode' => 'required|in:online,offline,hybrid',
            'prices.*.base_price' => 'required|numeric|min:0',
        ]);

        $service = Service::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        if (! empty($validated['prices'])) {
            foreach ($validated['prices'] as $priceData) {
                if ($priceData['base_price'] > 0) {
                    $service->pricing()->create([
                        'mode' => $priceData['mode'],
                        'base_price' => $priceData['base_price'],
                    ]);
                }
            }
        }

        return redirect()->route('services.index')->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        $service->load('pricing', 'modules', 'packages.modules');

        return Inertia::render('Services/Form', [
            'service' => $service,
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prices' => 'array',
            'prices.*.mode' => 'required|in:online,offline,hybrid',
            'prices.*.base_price' => 'required|numeric|min:0',
        ]);

        $service->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (isset($validated['prices'])) {
            $newModes = collect($validated['prices'])->pluck('mode')->toArray();
            
            $existingModesWithSubs = $service->subscriptions()->pluck('mode')->unique()->toArray();
            $removedModesInUse = array_diff($existingModesWithSubs, $newModes);

            if (!empty($removedModesInUse)) {
                return back()->withErrors(['prices' => 'Cannot remove mode(s): ' . implode(', ', $removedModesInUse) . ' because they are in use by subscriptions.']);
            }

            // Delete existing pricing to recreate or just update
            $service->pricing()->delete();
            foreach ($validated['prices'] as $priceData) {
                if ($priceData['base_price'] > 0) {
                    $service->pricing()->create([
                        'mode' => $priceData['mode'],
                        'base_price' => $priceData['base_price'],
                    ]);
                }
            }
        }

        return redirect()->route('services.index')->with('success', 'Service updated successfully.');
    }

    public function show(Service $service)
    {
        $service->load(['pricing', 'modules', 'packages.modules', 'subscriptions.business', 'creator']);

        return Inertia::render('Services/Show', [
            'service' => $service,
        ]);
    }

    public function destroy(Service $service)
    {
        if ($service->subscriptions()->exists()) {
            $service->delete();
        } else {
            $service->forceDelete();
        }

        return redirect()->route('services.index')->with('success', 'Service deleted successfully.');
    }
}
