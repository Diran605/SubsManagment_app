<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, RecordsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
