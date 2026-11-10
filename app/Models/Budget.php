<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * @return HasMany<BudgetPackage, $this>
     */
    public function budgetPackages(): HasMany
    {
        return $this->hasMany(BudgetPackage::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activityLabel(): string
    {
        return "Budget {$this->fiscal_year}";
    }
}
