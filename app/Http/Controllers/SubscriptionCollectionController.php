<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionCollection;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SubscriptionCollectionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:edit subscriptions'),
        ];
    }

    public function store(Request $request, Subscription $subscription)
    {
        $subscription->loadMissing('package');

        if ($subscription->package?->payer_type !== 'student_momo') {
            return back()->with('error', 'Mobile Money collections can only be recorded for student-funded subscriptions.');
        }

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
