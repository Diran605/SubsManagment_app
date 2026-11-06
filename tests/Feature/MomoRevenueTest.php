<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MomoRevenueTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $service = Service::factory()->create();

        // School pays the full 120 000.
        Subscription::factory()->create(['service_id' => $service->id, 'amount' => 120000]);

        // Students pay via MoMo: 500 000 target, 80 000 collected so far.
        $momoPackage = Package::create([
            'service_id' => $service->id,
            'name' => 'Per student MoMo',
            'price' => 1000,
            'pricing_model' => 'per_student',
            'payer_type' => 'student_momo',
        ]);
        $momo = Subscription::factory()->create([
            'service_id' => $service->id,
            'package_id' => $momoPackage->id,
            'amount' => 500000,
            'student_count' => 500,
        ]);
        $momo->collections()->create(['amount' => 50000, 'collection_date' => now()->toDateString()]);
        $momo->collections()->create(['amount' => 30000, 'collection_date' => now()->toDateString()]);
    }

    public function test_dashboard_counts_only_collected_momo_money_as_revenue()
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('totalRevenue', fn ($value) => (float) $value === 200000.0)
                ->where('momo.count', 1)
                ->where('momo.target', fn ($value) => (float) $value === 500000.0)
                ->where('momo.collected', fn ($value) => (float) $value === 80000.0)
            );
    }

    public function test_reports_count_only_collected_momo_money_as_revenue()
    {
        $this->actingAs($this->admin)
            ->get(route('reports.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('metrics.totalActiveRevenue', fn ($value) => (float) $value === 200000.0)
                ->where('metrics.totalHistoricalRevenue', fn ($value) => (float) $value === 200000.0)
                ->where('charts.revenueOverTime', fn ($months) => (float) collect($months)->sum() === 200000.0)
            );
    }
}
