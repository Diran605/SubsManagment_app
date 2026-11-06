<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Flat-price packages are priced per month, so a yearly budget target must be
 * licences × monthly price × months. Until now it was licences × monthly price
 * (one month), which made "% to goal" about 12× too high.
 *
 * Existing flat-price targets are recalculated for a full 12 months.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_packages', function (Blueprint $table) {
            $table->unsignedTinyInteger('expected_months')->default(12)->after('avg_student_count');
        });

        $rows = DB::table('budget_packages')
            ->join('packages', 'packages.id', '=', 'budget_packages.package_id')
            ->where('packages.pricing_model', '!=', 'per_student')
            ->select('budget_packages.id', 'budget_packages.expected_licenses', 'packages.price')
            ->get();

        foreach ($rows as $row) {
            DB::table('budget_packages')->where('id', $row->id)->update([
                'expected_amount' => round($row->expected_licenses * (float) $row->price * 12, 2),
            ]);
        }

        $this->recalculateBudgetTotals();
    }

    public function down(): void
    {
        $rows = DB::table('budget_packages')
            ->join('packages', 'packages.id', '=', 'budget_packages.package_id')
            ->where('packages.pricing_model', '!=', 'per_student')
            ->select('budget_packages.id', 'budget_packages.expected_licenses', 'packages.price')
            ->get();

        foreach ($rows as $row) {
            DB::table('budget_packages')->where('id', $row->id)->update([
                'expected_amount' => round($row->expected_licenses * (float) $row->price, 2),
            ]);
        }

        Schema::table('budget_packages', function (Blueprint $table) {
            $table->dropColumn('expected_months');
        });

        $this->recalculateBudgetTotals();
    }

    private function recalculateBudgetTotals(): void
    {
        foreach (DB::table('budgets')->pluck('id') as $budgetId) {
            DB::table('budgets')->where('id', $budgetId)->update([
                'total_expected_amount' => DB::table('budget_packages')->where('budget_id', $budgetId)->sum('expected_amount'),
            ]);
        }
    }
};
