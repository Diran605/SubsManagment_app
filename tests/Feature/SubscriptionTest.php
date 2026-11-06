<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Package;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use App\Services\BudgetReportService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');
    }

    public function test_status_filter_is_applied_in_the_database()
    {
        $active = Subscription::factory()->endingInDays(200)->create();
        $expiring = Subscription::factory()->endingInDays(10)->create();
        $expired = Subscription::factory()->endingInDays(-5)->create();
        $renewed = Subscription::factory()->endingInDays(-1)->create();
        // The renewal itself runs for a year, so it is "active".
        $renewal = Subscription::factory()->create(['renewed_from_id' => $renewed->id]);

        $cases = [
            'active' => [$active->id, $renewal->id],
            'expiring_soon' => [$expiring->id],
            'expired' => [$expired->id],
            'renewed' => [$renewed->id],
        ];

        foreach ($cases as $status => $expectedIds) {
            $this->actingAs($this->admin)
                ->get(route('subscriptions.index', ['status' => $status]))
                ->assertInertia(fn (Assert $page) => $page
                    ->where('subscriptions.data', function ($rows) use ($expectedIds, $status) {
                        $rows = collect($rows);

                        return $rows->pluck('id')->sort()->values()->all() === collect($expectedIds)->sort()->values()->all()
                            && $rows->every(fn ($row) => $row['status_badge'] === $status);
                    }));
        }
    }

    public function test_dates_are_serialized_as_plain_dates()
    {
        $subscription = Subscription::factory()->create([
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        $this->assertSame('2026-01-01', $subscription->toArray()['start_date']);
        $this->assertSame('2026-12-31', $subscription->toArray()['end_date']);
    }

    public function test_a_subscription_cannot_be_renewed_twice()
    {
        $original = Subscription::factory()->endingInDays(5)->create();

        $payload = [
            'amount' => 120000,
            'start_date' => now()->addDays(6)->toDateString(),
            'end_date' => now()->addDays(6)->addYear()->toDateString(),
        ];

        $this->actingAs($this->admin)->post(route('subscriptions.renew', $original), $payload);
        $this->actingAs($this->admin)->post(route('subscriptions.renew', $original), $payload);

        $this->assertSame(1, Subscription::where('renewed_from_id', $original->id)->count());
    }

    public function test_package_must_belong_to_the_selected_service()
    {
        $service = Service::factory()->create();
        $otherService = Service::factory()->create();
        $foreignPackage = Package::create([
            'service_id' => $otherService->id,
            'name' => 'Other',
            'price' => 1000,
            'supported_modes' => ['online'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('subscriptions.store'), [
                'business_id' => Business::factory()->create()->id,
                'service_id' => $service->id,
                'package_id' => $foreignPackage->id,
                'mode' => 'online',
                'amount' => 1000,
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
            ])
            ->assertSessionHasErrors('package_id');
    }

    public function test_subscriptions_can_be_created_without_a_package()
    {
        $this->actingAs($this->admin)
            ->post(route('subscriptions.store'), [
                'business_id' => Business::factory()->create()->id,
                'service_id' => Service::factory()->create()->id,
                'mode' => 'online',
                'amount' => 250000,
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('subscriptions', ['amount' => 250000, 'package_id' => null]);
    }

    public function test_budget_accrual_never_exceeds_the_subscription_amount()
    {
        $service = Service::factory()->create();
        $package = Package::create([
            'service_id' => $service->id,
            'name' => 'Flat',
            'price' => 10000,
            'supported_modes' => ['online'],
        ]);

        // A full calendar year: all of it accrues to 2025.
        Subscription::factory()->create([
            'service_id' => $service->id,
            'package_id' => $package->id,
            'amount' => 365000,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
        ]);

        $report = app(BudgetReportService::class)->generateReport(2025);
        $this->assertEqualsWithDelta(365000, $report['totals']['actual_amount'], 0.01);

        // Spanning two years: the amount is split by days, and the parts add up.
        Subscription::query()->delete();
        Subscription::factory()->create([
            'service_id' => $service->id,
            'package_id' => $package->id,
            'amount' => 365000,
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
        ]);

        $first = app(BudgetReportService::class)->generateReport(2026)['totals']['actual_amount'];
        $second = app(BudgetReportService::class)->generateReport(2027)['totals']['actual_amount'];

        $this->assertEqualsWithDelta(184000, $first, 0.01);   // Jul 1 – Dec 31 = 184 days
        $this->assertEqualsWithDelta(181000, $second, 0.01);  // Jan 1 – Jun 30 = 181 days
    }

    public function test_deleted_businesses_do_not_count_towards_dashboard_revenue()
    {
        $kept = Subscription::factory()->create(['amount' => 100000]);
        $gone = Subscription::factory()->create(['amount' => 900000]);
        $gone->business->delete();

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('totalRevenue', fn ($v) => (float) $v === 100000.0));

        $this->assertNotNull($kept);
    }
}
