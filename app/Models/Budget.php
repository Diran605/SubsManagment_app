<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use RecordsActivity;

    protected $fillable = [
        'fiscal_year',
        'total_expected_amount',
        'created_by',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'total_expected_amount' => 'decimal:2',
    ];

    public function budgetPackages()
    {
        return $this->hasMany(BudgetPackage::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activityLabel(): string
    {
        return "Budget {$this->fiscal_year}";
    }
}
