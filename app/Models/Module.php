<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    use RecordsActivity;

    protected $fillable = ['service_id', 'name', 'description'];

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsToMany<Package, $this>
     */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_modules');
    }

    /**
     * @return BelongsToMany<Subscription, $this>
     */
    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Subscription::class, 'subscription_modules');
    }
}
