<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Service;
use App\Services\BudgetReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function __construct(protected BudgetReportService $reportService) {}

    public function index(Request $request)
    {
        $currentYear = now()->year;
        $fiscalYear  = (int) $request->input('year', $currentYear);

        // Available years: 2 years back to 2 years forward
        $availableYears = range($currentYear - 2, $currentYear + 2);

        $report = $this->reportService->generateReport($fiscalYear);

        return Inertia::render('Budgets/Index', [
            'report'         => $report,
            'availableYears' => $availableYears,
            'selectedYear'   => $fiscalYear,
        ]);
    }

    public function create(Request $request)
    {
        $fiscalYear = (int) $request->input('year', now()->year);

        // Load existing budget for this year if any
        $existing = Budget::with('budgetPackages')
            ->where('fiscal_year', $fiscalYear)
            ->first();

        $services = Service::with('packages')->orderBy('name')->get();

        // Build a map of existing targets for easy lookup
        $existingTargets = [];
        if ($existing) {
            foreach ($existing->budgetPackages as $bp) {
                $existingTargets[$bp->package_id] = [
                    'expected_licenses' => $bp->expected_licenses,
                    'avg_student_count' => $bp->avg_student_count ?? 0,
                    'expected_amount'   => floatval($bp->expected_amount),
                ];
            }
        }

        return Inertia::render('Budgets/Create', [
            'fiscalYear'      => $fiscalYear,
            'services'        => $services,
            'existingTargets' => $existingTargets,
            'existingBudget'  => $existing,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'packages'    => 'required|array',
            'packages.*.package_id'        => 'required|exists:packages,id',
            'packages.*.expected_licenses' => 'required|integer|min:0',
            'packages.*.avg_student_count' => 'nullable|integer|min:0',
            'packages.*.expected_amount'   => 'required|numeric|min:0',
        ]);

        // Upsert the budget record
        $budget = Budget::updateOrCreate(
            ['fiscal_year' => $validated['fiscal_year']],
            [
                'total_expected_amount' => collect($validated['packages'])->sum('expected_amount'),
                'created_by'            => $request->user()->id,
            ]
        );

        // Sync all budget package targets
        foreach ($validated['packages'] as $pkg) {
            \App\Models\BudgetPackage::updateOrCreate(
                ['budget_id' => $budget->id, 'package_id' => $pkg['package_id']],
                [
                    'expected_licenses' => $pkg['expected_licenses'],
                    'avg_student_count' => $pkg['avg_student_count'] ?? null,
                    'expected_amount'   => $pkg['expected_amount'],
                ]
            );
        }

        // Remove any package targets that were zeroed out (not submitted)
        $submittedPackageIds = collect($validated['packages'])->pluck('package_id')->toArray();
        $budget->budgetPackages()
            ->whereNotIn('package_id', $submittedPackageIds)
            ->delete();

        return redirect()
            ->route('budgets.index', ['year' => $validated['fiscal_year']])
            ->with('success', "Budget for {$validated['fiscal_year']} saved successfully.");
    }
}
