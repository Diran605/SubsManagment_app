<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    /**
     * Record types that can be filtered on, keyed by activity log name.
     */
    private const TYPES = [
        'business' => 'Businesses',
        'service' => 'Services',
        'package' => 'Packages',
        'module' => 'Modules',
        'subscription' => 'Subscriptions',
        'subscription_collection' => 'MoMo collections',
        'budget' => 'Budgets',
        'budget_package' => 'Budget lines',
        'user' => 'Staff accounts',
        'role' => 'Roles',
    ];

    private const EVENTS = ['created', 'updated', 'deleted', 'restored', 'renewed', 'roles_changed'];

    /**
     * Fields that are never worth showing in the "changes" column.
     */
    private const HIDDEN_FIELDS = ['id', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'recorded_by'];

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'type' => 'nullable|string|in:'.implode(',', array_keys(self::TYPES)),
            'event' => 'nullable|string|in:'.implode(',', self::EVENTS),
            'user' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $logs = Activity::query()
            ->with('causer')
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('log_name', $type))
            ->when($filters['event'] ?? null, fn ($q, $event) => $q->where('event', $event))
            ->when($filters['user'] ?? null, fn ($q, $user) => $q->where('causer_type', (new User)->getMorphClass())->where('causer_id', $user))
            ->when($filters['date_from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['date_to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Activity $activity): array => [
                'id' => $activity->id,
                'event' => $activity->event ?? $activity->description,
                'type' => $activity->log_name,
                'type_label' => self::TYPES[$activity->log_name] ?? ucfirst((string) $activity->log_name),
                'label' => $activity->properties['label'] ?? '#'.$activity->subject_id,
                'user' => $activity->causer instanceof User ? $activity->causer->name : null,
                'created_at' => $activity->created_at?->toIso8601String(),
                'changes' => $this->changes($activity),
            ]);

        return Inertia::render('AuditLogs/Index', [
            'logs' => $logs,
            'filters' => [
                'type' => $filters['type'] ?? null,
                'event' => $filters['event'] ?? null,
                'user' => isset($filters['user']) ? (int) $filters['user'] : null,
                'date_from' => $filters['date_from'] ?? null,
                'date_to' => $filters['date_to'] ?? null,
            ],
            'types' => collect(self::TYPES)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values(),
            'events' => self::EVENTS,
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Turn the logged old/new attributes into a list of field changes.
     *
     * @return Collection<int, array{field: string, old: string|null, new: string|null}>
     */
    private function changes(Activity $activity): Collection
    {
        $new = Arr::except((array) ($activity->properties['attributes'] ?? []), self::HIDDEN_FIELDS);
        $old = Arr::except((array) ($activity->properties['old'] ?? []), self::HIDDEN_FIELDS);

        return collect(array_unique([...array_keys($new), ...array_keys($old)]))
            ->map(fn (string $field) => [
                'field' => str_replace('_', ' ', $field),
                'old' => $this->display($old[$field] ?? null),
                'new' => $this->display($new[$field] ?? null),
            ])
            ->values();
    }

    private function display(mixed $value): ?string
    {
        return match (true) {
            $value === null => null,
            is_bool($value) => $value ? 'Yes' : 'No',
            is_array($value) => implode(', ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $value)),
            default => (string) $value,
        };
    }
}
