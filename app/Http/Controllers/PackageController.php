<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PackageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:create packages', only: ['store']),
            new Middleware('permission:edit packages', only: ['update']),
            new Middleware('permission:delete packages', only: ['destroy']),
        ];
    }

    public function store(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'online_price' => 'nullable|numeric|min:0',
            'offline_price' => 'nullable|numeric|min:0',
            'hybrid_price' => 'nullable|numeric|min:0',
            'modules' => 'array',
            'modules.*' => 'exists:modules,id',
        ]);

        $package = $service->packages()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'online_price' => $validated['online_price'] ?? null,
            'offline_price' => $validated['offline_price'] ?? null,
            'hybrid_price' => $validated['hybrid_price'] ?? null,
        ]);

        if (isset($validated['modules'])) {
            $package->modules()->sync($validated['modules']);
        }

        return back()->with('success', 'Package added successfully.');
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'online_price' => 'nullable|numeric|min:0',
            'offline_price' => 'nullable|numeric|min:0',
            'hybrid_price' => 'nullable|numeric|min:0',
            'modules' => 'array',
            'modules.*' => 'exists:modules,id',
        ]);

        $package->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'online_price' => $validated['online_price'] ?? null,
            'offline_price' => $validated['offline_price'] ?? null,
            'hybrid_price' => $validated['hybrid_price'] ?? null,
        ]);

        if (isset($validated['modules'])) {
            $package->modules()->sync($validated['modules']);
        }

        return back()->with('success', 'Package updated successfully.');
    }

    public function destroy(Package $package)
    {
        if ($package->subscriptions()->exists()) {
            return back()->withErrors(['package' => 'Cannot permanently delete this package because subscriptions reference it.']);
        }

        $package->delete(); // Or forceDelete depending on if we use SoftDeletes for packages.

        return back()->with('success', 'Package removed successfully.');
    }
}
