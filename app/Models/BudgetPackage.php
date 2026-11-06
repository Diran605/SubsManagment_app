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
        'expected_months',
        'expected_amount',
    ];

    protected $casts = [
        'expected_licenses' => 'integer',
        'avg_student_count' => 'integer',
        'expected_months' => 'integer',
        'expected_amount' => 'decimal:2',
    ];

    /**
     * Expected revenue for one budget line.
     *
     * - Per-student packages are priced per student for the contract:
     *   schools × average students × price.
     * - Flat packages are priced per month:
     *   licences × monthly price × months.
     *
     * Keep in sync with getExpectedAmount() in resources/js/pages/Budgets/Create.vue.
     */
    public static function calculateExpectedAmount(Package $package, int $licenses, ?int $avgStudents, int $months): float
    {
        $amount = $package->pricing_model === 'per_student'
            ? $licenses * (int) $avgStudents * (float) $package->price
            : $licenses * (float) $package->price * $months;

        return round($amount, 2);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
