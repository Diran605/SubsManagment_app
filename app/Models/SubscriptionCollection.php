<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionCollection extends Model
{
    use RecordsActivity;

    protected $fillable = [
        'subscription_id',
        'amount',
        'collection_date',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'collection_date' => 'date:Y-m-d',
    ];

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function activityLabel(): string
    {
        $amount = number_format((float) $this->amount, 0, '.', ',');
        $for = $this->subscription?->activityLabel() ?? 'subscription #'.$this->subscription_id;

        return "MoMo payment of {$amount} FCFA for {$for}";
    }
}
