<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Service;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BudgetReportService
{
    /**
     * Generate a full budget report for a given fiscal year.
     */
    public function generateReport(int $fiscalYear): array
    {
        $yearStart = Carbon::create($fiscalYear, 1, 1)->startOfDay();
        $yearEnd   = Carbon::create($fiscalYear, 12, 31)->endOfDay();

        // Load the budget for this year (if one exists)
        $budget = Budget::with('budgetPackages.package.service')
            ->where('fiscal_year', $fiscalYear)
            ->first();

        // Load all subscriptions that overlap with this fiscal year
        $subscriptions = Subscription::with(['package', 'package.service', 'collections'])
            ->whereNotNull('package_id')
            ->where('start_date', '<=', $yearEnd)
            ->where('end_date', '>=', $yearStart)
            ->whereNull('deleted_at')
            ->get();

        // ── Accrual calculation ──────────────────────────────────────────────
        // Group accrued revenue by package_id
        $accruedByPackage = [];
        $licensesByPackage = [];

        foreach ($subscriptions as $sub) {
            $subStart = Carbon::parse($sub->start_date)->startOfDay();
            $subEnd   = Carbon::parse($sub->end_date)->endOfDay();

            // If this is a student MoMo funded package (Tier 3), revenue is recognized
            // by actual collections received within the fiscal year window
            if ($sub->package && $sub->package->payer_type === 'student_momo') {
                $collectedInYear = $sub->collections
                    ->filter(function ($col) use ($yearStart, $yearEnd) {
                        $colDate = Carbon::parse($col->collection_date);
                        return $colDate->gte($yearStart) && $colDate->lte($yearEnd);
                    })
                    ->sum('amount');
                $accruedAmount = round($collectedInYear, 2);
            } else {
                // Standard institutional daily pro-rata accrual
                $totalDays   = $subStart->diffInDays($subEnd) ?: 1;
                $dailyRate   = floatval($sub->amount) / $totalDays;

                // Clip to fiscal year window
                $effectiveStart = $subStart->lt($yearStart) ? $yearStart->copy() : $subStart->copy();
                $effectiveEnd   = $subEnd->gt($yearEnd) ? $yearEnd->copy() : $subEnd->copy();
                $daysInYear     = $effectiveStart->diffInDays($effectiveEnd) + 1;

                $accruedAmount = round($dailyRate * $daysInYear, 2);
            }

            $pkgId = $sub->package_id;
            $accruedByPackage[$pkgId] = ($accruedByPackage[$pkgId] ?? 0) + $accruedAmount;
            $licensesByPackage[$pkgId] = ($licensesByPackage[$pkgId] ?? 0) + 1;
        }

        // ── Build service-level rows ──────────────────────────────────────────
        $services = Service::with('packages')->orderBy('name')->get();

        $rows = [];
        $totalExpected = 0;
        $totalActual   = 0;
        $totalExpectedLicenses = 0;
        $totalCurrentLicenses  = 0;

        foreach ($services as $service) {
            $serviceExpected         = 0;
            $serviceActual           = 0;
            $serviceExpectedLicenses = 0;
            $serviceCurrentLicenses  = 0;

            foreach ($service->packages as $pkg) {
                $budgetPkg = $budget
                    ? $budget->budgetPackages->firstWhere('package_id', $pkg->id)
                    : null;

                $expectedLicenses = $budgetPkg ? $budgetPkg->expected_licenses : 0;
                $expectedAmount   = $budgetPkg ? floatval($budgetPkg->expected_amount) : 0;
                $currentLicenses  = $licensesByPackage[$pkg->id] ?? 0;
                $actualAmount     = $accruedByPackage[$pkg->id] ?? 0;

                $serviceExpectedLicenses += $expectedLicenses;
                $serviceCurrentLicenses  += $currentLicenses;
                $serviceExpected         += $expectedAmount;
                $serviceActual           += $actualAmount;
            }

            // Only include services that have budget targets OR actual activity
            if ($serviceExpected > 0 || $serviceActual > 0) {
                $rows[] = [
                    'fiscal_year'          => $fiscalYear,
                    'service_id'           => $service->id,
                    'service_name'         => $service->name,
                    'current_licenses'     => $serviceCurrentLicenses,
                    'expected_licenses'    => $serviceExpectedLicenses,
                    'actual_amount'        => $serviceActual,
                    'expected_amount'      => $serviceExpected,
                ];

                $totalExpected         += $serviceExpected;
                $totalActual           += $serviceActual;
                $totalExpectedLicenses += $serviceExpectedLicenses;
                $totalCurrentLicenses  += $serviceCurrentLicenses;
            }
        }

        $totalBudgetExpected = $budget ? floatval($budget->total_expected_amount) : $totalExpected;
        $percentToGoal = $totalBudgetExpected > 0
            ? round(($totalActual / $totalBudgetExpected) * 100, 1)
            : 0;

        return [
            'fiscal_year'          => $fiscalYear,
            'budget'               => $budget,
            'rows'                 => $rows,
            'totals' => [
                'expected_amount'      => $totalBudgetExpected,
                'actual_amount'        => $totalActual,
                'expected_licenses'    => $totalExpectedLicenses,
                'current_licenses'     => $totalCurrentLicenses,
                'percent_to_goal'      => $percentToGoal,
                'variance'             => $totalBudgetExpected - $totalActual,
            ],
        ];
    }
}
