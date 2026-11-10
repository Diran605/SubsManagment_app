<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DetailPagesTest extends TestCase
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

    public function test_business_page_lists_subscriptions_with_status_newest_first()
    {
        $business = Business::factory()->create();
        $old = Subscription::factory()->for($business)->create([
            'start_date' => now()->subYears(2)->toDateString(),
            'end_date' => now()->subYear()->toDateString(),
        ]);
        $renewal = Subscription::factory()->for($business)->create([
            'start_date' => now()->subYear()->addDay()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'renewed_from_id' => $old->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('businesses.show', $business))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Businesses/Show')
                ->where('business.subscriptions.0.id', $renewal->id)
                ->where('business.subscriptions.0.status_badge', 'active')
                ->where('business.subscriptions.1.status_badge', 'renewed')
            );
    }

    public function test_staff_page_lists_recorded_subscriptions_with_client_and_status()
    {
        $subscription = Subscription::factory()->create([
            'created_by' => $this->admin->id,
            'end_date' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('staff.show', $this->admin))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Staff/Show')
                ->where('staff.subscriptions.0.business.name', $subscription->business->name)
                ->where('staff.subscriptions.0.status_badge', 'expired')
            );
    }
}
