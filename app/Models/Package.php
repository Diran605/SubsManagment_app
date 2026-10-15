<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'service_id',
        'name',
        'description',
        'price',
        'pricing_model',
        'payer_type',
        'supported_modes',
    ];

    protected $casts = [
        'supported_modes' => 'array',
        'price' => 'decimal:2',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'package_modules');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
