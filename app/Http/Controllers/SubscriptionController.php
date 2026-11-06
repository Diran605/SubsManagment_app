<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Package;
use App\Models\Service;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SubscriptionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view subscriptions', only: ['index', 'show']),
            new Middleware('permission:create subscriptions', only: ['create', 'store', 'createRenewal', 'renew']),
            new Middleware('permission:edit subscriptions', only: ['edit', 'update']),
            new Middleware('permission:delete subscriptions', only: ['destroy']),
            new Middleware('permission:restore subscriptions', only: ['restore']),
        ];
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Subscription::with(['business', 'service', 'creator'])
            ->withExists('renewals');

        if ($statusFilter === 'trashed') {
            $query->onlyTrashed();
        } elseif ($statusFilter === 'all') {
            $query->withTrashed();
        } elseif (in_array($statusFilter, Subscription::STATUSES, true)) {
            $query->whereStatus($statusFilter);
        }

        $subscriptions = $query
            ->when($search, fn ($q) => $q->whereHas('business', fn ($b) => $b->where('name', 'like', "%{$search}%")))
            ->when($dateFrom, fn ($q) => $q->whereDate('start_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('start_date', '<=', $dateTo))
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(function (Subscription $sub) {
                $sub->status_badge = $sub->statusBadge();

                return $sub;
            });

        return Inertia::render('Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function create(Request $request)
    {
        $businessId = $request->input('business_id');

        return Inertia::render('Subscriptions/Form', [
            'businesses' => Business::orderBy('name')->get(['id', 'name']),
            'services' => Service::with(['modules', 'packages.modules'])->orderBy('name')->get(),
            'defaultBusinessId' => $businessId ? (int) $businessId : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSubscription($request);

        DB::transaction(function () use ($validated, $request) {
            $subscription = Subscription::create([
                ...collect($validated)->except('modules')->all(),
                'created_by' => $request->user()->id,
            ]);

            $subscription->modules()->sync($validated['modules'] ?? []);
        });

        return redirect()->route('subscriptions.index')->with('success', 'Subscription recorded successfully.');
    }

    public function createRenewal(Subscription $subscription)
    {
        if ($subscription->renewals()->exists()) {
            return redirect()->route('subscriptions.show', $subscription)
                ->with('error', 'This subscription has already been renewed.');
        }

        $subscription->load(['business', 'service.modules', 'service.packages.modules', 'modules']);

        return Inertia::render('Subscriptions/Form', [
            'businesses' => Business::orderBy('name')->get(['id', 'name']),
            'services' => Service::with(['modules', 'packages.modules'])->orderBy('name')->get(),
            'renewing' => $subscription,
        ]);
    }

    public function renew(Request $request, Subscription $subscription)
    {
        // Business, service and mode carry over from the original subscription.
        $request->merge([
            'business_id' => $subscription->business_id,
            'service_id' => $subscription->service_id,
            'mode' => $subscription->mode,
        ]);

        $validated = $this->validateSubscription($request);

        $renewed = DB::transaction(function () use ($validated, $request, $subscription) {
            // Lock the original row so two simultaneous renewals can't both succeed.
            $original = Subscription::whereKey($subscription->id)->lockForUpdate()->first();

            if ($original->renewals()->exists()) {
                return false;
            }

            $newSub = Subscription::create([
                ...collect($validated)->except('modules')->all(),
                'renewed_from_id' => $original->id,
                'created_by' => $request->user()->id,
            ]);

            $newSub->modules()->sync($validated['modules'] ?? []);

            activity('subscription')
                ->performedOn($original)
                ->event('renewed')
                ->withProperties([
                    'attributes' => ['renewal_id' => $newSub->id, 'end_date' => $newSub->end_date->toDateString()],
                    'label' => $original->activityLabel(),
                ])
                ->log('renewed');

            return true;
        });

        if (! $renewed) {
            return redirect()->route('subscriptions.show', $subscription)
                ->with('error', 'This subscription has already been renewed.');
        }

        return redirect()->route('subscriptions.index')->with('success', 'Subscription renewed successfully.');
    }

    public function show($id)
    {
        $subscription = Subscription::withTrashed()->with([
            'business',
            'service',
            'package',
            'modules',
            'creator',
            'renewals',
            'collections' => fn ($query) => $query->orderByDesc('collection_date'),
            'collections.recorder' => fn ($query) => $query->select('id', 'name', 'email'),
        ])->findOrFail($id);

        $subscription->status_badge = $subscription->statusBadge();

        return Inertia::render('Subscriptions/Show', [
            'subscription' => $subscription,
        ]);
    }

    public function edit(Subscription $subscription)
    {
        $subscription->load('modules');

        return Inertia::render('Subscriptions/Form', [
            'businesses' => Business::orderBy('name')->get(['id', 'name']),
            'services' => Service::with(['modules', 'packages.modules'])->orderBy('name')->get(),
            'subscription' => $subscription,
        ]);
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $this->validateSubscription($request);

        DB::transaction(function () use ($validated, $subscription) {
            $subscription->update(collect($validated)->except('modules')->all());
            $subscription->modules()->sync($validated['modules'] ?? []);
        });

        return redirect()->route('subscriptions.index')->with('success', 'Subscription updated successfully.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return redirect()->back()->with('success', 'Subscription deleted successfully.');
    }

    public function restore($id)
    {
        $subscription = Subscription::withTrashed()->findOrFail($id);
        $subscription->restore();

        return redirect()->back()->with('success', 'Subscription restored successfully.');
    }

    /**
     * Validate subscription input, including checks that the package and
     * modules belong to the chosen service and match the package's rules.
     *
     * @return array<string, mixed>
     */
    protected function validateSubscription(Request $request): array
    {
        $serviceId = $request->input('service_id');

        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'service_id' => 'required|exists:services,id',
            'mode' => 'required|in:online,offline,hybrid',
            'amount' => 'required|numeric|min:0',
            'student_count' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'package_id' => [
                'nullable',
                Rule::exists('packages', 'id')->where('service_id', $serviceId),
            ],
            'modules' => 'nullable|array',
            'modules.*' => [
                Rule::exists('modules', 'id')->where('service_id', $serviceId),
            ],
        ], [
            'package_id.exists' => 'The selected package does not belong to this service.',
            'modules.*.exists' => 'One of the selected modules does not belong to this service.',
        ]);

        $package = isset($validated['package_id']) ? Package::find($validated['package_id']) : null;

        if ($package) {
            $modes = $package->supported_modes ?? [];

            if (! empty($modes) && ! in_array($validated['mode'], $modes, true)) {
                throw ValidationException::withMessages([
                    'mode' => 'This package does not support the "'.$validated['mode'].'" mode.',
                ]);
            }

            if ($package->pricing_model === 'per_student' && empty($validated['student_count'])) {
                throw ValidationException::withMessages([
                    'student_count' => 'The student count is required for per-student packages.',
                ]);
            }
        }

        $validated['student_count'] = $validated['student_count'] ?? null;
        $validated['package_id'] = $validated['package_id'] ?? null;
        $validated['notes'] = $validated['notes'] ?? null;

        return $validated;
    }
}
