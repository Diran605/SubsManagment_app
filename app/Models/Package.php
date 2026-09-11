<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = ['service_id', 'name', 'description', 'reference_price'];

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
