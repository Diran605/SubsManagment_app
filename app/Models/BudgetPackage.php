<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetPackage extends Model
{
    protected $fillable = [
        'budget_id',
        'package_id',
        'expected_licenses',
        'avg_student_count',
        'expected_amount',
    ];

    protected $casts = [
        'expected_licenses' => 'integer',
        'avg_student_count' => 'integer',
        'expected_amount' => 'decimal:2',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
