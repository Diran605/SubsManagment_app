<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pricing()
    {
        return $this->hasMany(ServicePricing::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }
}
