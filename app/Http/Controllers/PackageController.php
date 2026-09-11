<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function store(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reference_price' => 'nullable|numeric|min:0',
            'modules' => 'array',
            'modules.*' => 'exists:modules,id',
        ]);

        $package = $service->packages()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'reference_price' => $validated['reference_price'] ?? null,
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
            'reference_price' => 'nullable|numeric|min:0',
            'modules' => 'array',
            'modules.*' => 'exists:modules,id',
        ]);

        $package->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'reference_price' => $validated['reference_price'] ?? null,
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
