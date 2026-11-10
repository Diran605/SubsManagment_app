<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

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

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function recorder()
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
