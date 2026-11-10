<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Package;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_budget_targets_use_months_for_flat_packages_only()
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $service = Service::factory()->create();
        $flat = Package::create([
            'service_id' => $service->id,
            'name' => 'Monthly',
            'price' => 10000,
            'pricing_model' => 'flat',
        ]);
        $perStudent = Package::create([
            'service_id' => $service->id,
            'name' => 'Per student',
            'price' => 1500,
            'pricing_model' => 'per_student',
        ]);

        $this->actingAs($admin)
            ->post(route('budgets.store'), [
                'fiscal_year' => 2027,
                'packages' => [
                    // 2 licences × 10 000 × 6 months
                    ['package_id' => $flat->id, 'expected_licenses' => 2, 'expected_months' => 6],
                    // 3 schools × 400 students × 1 500 (months are ignored)
                    ['package_id' => $perStudent->id, 'expected_licenses' => 3, 'avg_student_count' => 400, 'expected_months' => 6],
                ],
            ])
            ->assertSessionHasNoErrors();

        $budget = Budget::with('budgetPackages')->where('fiscal_year', 2027)->firstOrFail();

        $this->assertEquals(120000, $budget->budgetPackages->firstWhere('package_id', $flat->id)->expected_amount);
        $this->assertEquals(1800000, $budget->budgetPackages->firstWhere('package_id', $perStudent->id)->expected_amount);
        $this->assertEquals(1920000, $budget->total_expected_amount);
    }

    public function test_months_default_to_twelve()
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $package = Package::create([
            'service_id' => Service::factory()->create()->id,
            'name' => 'Monthly',
            'price' => 10000,
            'pricing_model' => 'flat',
        ]);

        $this->actingAs($admin)->post(route('budgets.store'), [
            'fiscal_year' => 2027,
            'packages' => [['package_id' => $package->id, 'expected_licenses' => 2]],
        ]);

        $this->assertEquals(240000, Budget::where('fiscal_year', 2027)->value('total_expected_amount'));
    }
}
