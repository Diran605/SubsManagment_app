<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ServiceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view services', only: ['index', 'show']),
            new Middleware('permission:create services', only: ['create', 'store']),
            new Middleware('permission:edit services', only: ['edit', 'update']),
            new Middleware('permission:delete services', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $services = Service::with(['modules', 'packages'])->latest()->get();

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
            'category' => 'nullable|string|in:standard,education',
        ]);

        $service = Service::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? 'standard',
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('services.show', $service)->with('success', 'Service created! You can now add modules and packages below.');
    }

    public function edit(Service $service)
    {
        $service->load('modules', 'packages.modules');

        return Inertia::render('Services/Form', [
            'service' => $service,
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|in:standard,education',
        ]);

        $service->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? 'standard',
        ]);

        return redirect()->route('services.index')->with('success', 'Service updated successfully.');
    }

    public function show(Service $service)
    {
        $service->load(['modules', 'packages.modules', 'subscriptions.business', 'creator']);

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
