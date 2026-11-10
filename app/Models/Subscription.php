<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory, RecordsActivity, SoftDeletes;

    protected $fillable = [
        'business_id',
        'service_id',
        'mode',
        'amount',
        'student_count',
        'start_date',
        'end_date',
        'notes',
        'renewed_from_id',
        'created_by',
        'package_id',
    ];

    /**
     * Statuses a subscription can be filtered by. "trashed" is handled separately
     * because it depends on soft deletes rather than dates.
     */
    public const STATUSES = ['active', 'expiring_soon', 'expired', 'renewed'];

    protected $casts = [
        // Serialize as plain dates so <input type="date"> fields can display them.
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'amount' => 'decimal:2',
        'student_count' => 'integer',
    ];

    /**
     * Computed status accessor
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (! isset($attributes['end_date'])) {
                    return 'unknown';
                }

                $endDate = Carbon::parse($attributes['end_date'])->startOfDay();
                $today = Carbon::today();

                if ($endDate->isBefore($today)) {
                    return 'expired';
                }

                $threshold = config('subscription.expiring_threshold', 30);

                // Calculate absolute days remaining
                $daysRemaining = $today->diffInDays($endDate);

                if ($daysRemaining <= $threshold) {
                    return 'expiring_soon';
                }

                return 'active';
            }
        );
    }

    /**
     * The badge shown in the UI: trashed, renewed, or the date-based status.
     */
    public function statusBadge(): string
    {
        if ($this->trashed()) {
            return 'trashed';
        }

        $hasRenewals = $this->renewals_exists ?? $this->renewals->isNotEmpty();

        return $hasRenewals ? 'renewed' : $this->status;
    }

    /**
     * Filter by the same status rules as statusBadge(), but in the database.
     *
     * @param  Builder<Subscription>  $query
     */
    public function scopeWhereStatus(Builder $query, string $status): void
    {
        $today = Carbon::today()->toDateString();
        $threshold = Carbon::today()->addDays((int) config('subscription.expiring_threshold', 30))->toDateString();

        match ($status) {
            'renewed' => $query->whereHas('renewals'),
            'expired' => $query->whereDoesntHave('renewals')->whereDate('end_date', '<', $today),
            'expiring_soon' => $query->whereDoesntHave('renewals')
                ->whereDate('end_date', '>=', $today)
                ->whereDate('end_date', '<=', $threshold),
            'active' => $query->whereDoesntHave('renewals')->whereDate('end_date', '>', $threshold),
            default => null,
        };
    }

    /**
     * Subscriptions that currently bring in revenue: not superseded by a
     * renewal, not yet expired, and belonging to a business that still exists.
     *
     * @param  Builder<Subscription>  $query
     */
    public function scopeCurrentlyActive(Builder $query): void
    {
        $query->whereDoesntHave('renewals')
            ->whereDate('end_date', '>=', Carbon::today()->toDateString())
            ->whereHas('business', fn (Builder $q) => $q->whereNull('businesses.deleted_at'));
    }

    public function business()
    {
        return $this->belongsTo(Business::class)->withTrashed();
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function renewedFrom()
    {
        return $this->belongsTo(Subscription::class, 'renewed_from_id');
    }

    public function renewals()
    {
        return $this->hasMany(Subscription::class, 'renewed_from_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'subscription_modules');
    }

    public function collections()
    {
        return $this->hasMany(SubscriptionCollection::class);
    }

    /**
     * Student-funded ("Student MoMo") subscriptions are paid by each student via
     * Mobile Money, so their amount is only a target, not money owed.
     */
    public function isStudentFunded(): bool
    {
        return $this->package?->payer_type === 'student_momo';
    }

    /**
     * Money this subscription actually brings in: the agreed amount for
     * school-paid subscriptions, and only what has been collected for
     * student-funded ones. Load with withSum('collections', 'amount') to avoid
     * one query per subscription.
     */
    public function realisedRevenue(): float
    {
        if (! $this->isStudentFunded()) {
            return (float) $this->amount;
        }

        return (float) ($this->collections_sum_amount
            ?? ($this->relationLoaded('collections')
                ? $this->collections->sum('amount')
                : $this->collections()->sum('amount')));
    }

    public function activityLabel(): string
    {
        $business = $this->business?->name ?? 'Unknown business';
        $service = $this->service?->name ?? 'Unknown service';

        return "{$business} – {$service}";
    }
}
