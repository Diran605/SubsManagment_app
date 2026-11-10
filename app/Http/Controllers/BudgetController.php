<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetPackage;
use App\Models\Package;
use App\Models\Service;
use App\Services\BudgetReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function __construct(protected BudgetReportService $reportService) {}

    public function index(Request $request): Response
    {
        $currentYear = now()->year;
        $fiscalYear = (int) $request->input('year', $currentYear);

        // Available years: 2 years back to 2 years forward
        $availableYears = range($currentYear - 2, $currentYear + 2);

        $report = $this->reportService->generateReport($fiscalYear);

        return Inertia::render('Budgets/Index', [
            'report' => $report,
            'availableYears' => $availableYears,
            'selectedYear' => $fiscalYear,
        ]);
    }

    public function create(Request $request): Response
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
                    'expected_months' => $bp->expected_months ?? 12,
                    'expected_amount' => floatval($bp->expected_amount),
                ];
            }
        }

        return Inertia::render('Budgets/Create', [
            'fiscalYear' => $fiscalYear,
            'services' => $services,
            'existingTargets' => $existingTargets,
            'existingBudget' => $existing,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'packages' => 'present|array',
            'packages.*.package_id' => 'required|exists:packages,id',
            'packages.*.expected_licenses' => 'required|integer|min:0',
            'packages.*.avg_student_count' => 'nullable|integer|min:0',
            'packages.*.expected_months' => 'nullable|integer|min:1|max:12',
        ]);

        $packages = Package::whereIn('id', collect($validated['packages'])->pluck('package_id'))
            ->get()
            ->keyBy('id');

        // Recalculate each target from the package price rather than trusting
        // the amount calculated in the browser.
        $targets = collect($validated['packages'])->map(function (array $row) use ($packages) {
            $package = $packages[$row['package_id']];
            $licenses = (int) $row['expected_licenses'];
            $isPerStudent = $package->pricing_model === 'per_student';
            $avgStudents = $isPerStudent ? (int) ($row['avg_student_count'] ?? 0) : null;
            // Months only apply to flat (monthly) pricing.
            $months = $isPerStudent ? 12 : (int) ($row['expected_months'] ?? 12);

            return [
                'package_id' => $package->id,
                'expected_licenses' => $licenses,
                'avg_student_count' => $avgStudents,
                'expected_months' => $months,
                'expected_amount' => BudgetPackage::calculateExpectedAmount($package, $licenses, $avgStudents, $months),
            ];
        });

        DB::transaction(function () use ($validated, $targets, $request) {
            $budget = Budget::firstOrNew(['fiscal_year' => $validated['fiscal_year']]);
            $budget->total_expected_amount = $targets->sum('expected_amount');
            $budget->created_by ??= $request->user()->id;
            $budget->save();

            foreach ($targets as $target) {
                BudgetPackage::updateOrCreate(
                    ['budget_id' => $budget->id, 'package_id' => $target['package_id']],
                    collect($target)->except('package_id')->all(),
                );
            }

            // Remove package targets that were zeroed out (not submitted)
            $budget->budgetPackages()
                ->whereNotIn('package_id', $targets->pluck('package_id'))
                ->delete();
        });

        return redirect()
            ->route('budgets.index', ['year' => $validated['fiscal_year']])
            ->with('success', "Budget for {$validated['fiscal_year']} saved successfully.");
    }
}
