<?php

namespace App\Models;

use Database\Factories\ServicePricingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePricing extends Model
{
    /** @use HasFactory<ServicePricingFactory> */
    use HasFactory;

    protected $fillable = [
        'service_id',
        'mode',
        'base_price',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
