<?php

namespace App\Models;

use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'service_id',
        'mode',
        'amount',
        'start_date',
        'end_date',
        'notes',
        'renewed_from_id',
        'created_by',
        'package_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
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
}
