<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['service_id', 'name', 'description'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_modules');
    }

    public function subscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'subscription_modules');
    }
}
