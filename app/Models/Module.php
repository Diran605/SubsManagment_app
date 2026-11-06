<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use RecordsActivity;

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
