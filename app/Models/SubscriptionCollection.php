<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionCollection extends Model
{
    protected $fillable = [
        'subscription_id',
        'amount',
        'collection_date',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'collection_date' => 'date',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
