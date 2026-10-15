<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionCollection;
use Illuminate\Http\Request;

class SubscriptionCollectionController extends Controller
{
    public function store(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'collection_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $subscription->collections()->create([
            'amount' => $validated['amount'],
            'collection_date' => $validated['collection_date'],
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Mobile Money collection recorded successfully.');
    }

    public function destroy(SubscriptionCollection $collection)
    {
        $collection->delete();

        return back()->with('success', 'Collection entry removed successfully.');
    }
}
