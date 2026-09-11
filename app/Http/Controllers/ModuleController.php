<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function store(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $service->modules()->create($validated);

        return back()->with('success', 'Module added successfully.');
    }

    public function update(Request $request, Module $module)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $module->update($validated);

        return back()->with('success', 'Module updated successfully.');
    }

    public function destroy(Module $module)
    {
        if ($module->subscriptions()->exists()) {
            return back()->withErrors(['module' => 'Cannot remove module because it is in use by active subscriptions.']);
        }

        $module->delete();

        return back()->with('success', 'Module removed successfully.');
    }
}
