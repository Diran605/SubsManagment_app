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
            'price' => 'required|numeric|min:0',
            'pricing_model' => 'nullable|string|in:flat,per_student',
            'payer_type' => 'nullable|string|in:school,student_momo',
            'supported_modes' => 'nullable|array',
            'supported_modes.*' => 'in:online,offline,hybrid',
            'modules' => 'array',
            'modules.*' => 'exists:modules,id',
        ]);

        $package = $service->packages()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'pricing_model' => $validated['pricing_model'] ?? 'flat',
            'payer_type' => $validated['payer_type'] ?? 'school',
            'supported_modes' => $validated['supported_modes'] ?? [],
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
            'price' => 'required|numeric|min:0',
            'pricing_model' => 'nullable|string|in:flat,per_student',
            'payer_type' => 'nullable|string|in:school,student_momo',
            'supported_modes' => 'nullable|array',
            'supported_modes.*' => 'in:online,offline,hybrid',
            'modules' => 'array',
            'modules.*' => 'exists:modules,id',
        ]);

        $package->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'pricing_model' => $validated['pricing_model'] ?? 'flat',
            'payer_type' => $validated['payer_type'] ?? 'school',
            'supported_modes' => $validated['supported_modes'] ?? [],
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
