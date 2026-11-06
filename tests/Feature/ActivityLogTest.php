<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create(['name' => 'Ada Admin']);
        $this->admin->assignRole('Admin');
    }

    public function test_updating_a_subscription_records_who_changed_what()
    {
        $subscription = Subscription::factory()->create(['amount' => 1000]);

        $this->actingAs($this->admin)->put(route('subscriptions.update', $subscription), [
            'business_id' => $subscription->business_id,
            'service_id' => $subscription->service_id,
            'mode' => 'online',
            'amount' => 2500,
            'start_date' => $subscription->start_date->toDateString(),
            'end_date' => $subscription->end_date->toDateString(),
        ])->assertSessionHasNoErrors();

        $activity = Activity::where('log_name', 'subscription')->where('event', 'updated')->latest('id')->firstOrFail();

        $this->assertTrue($activity->causer->is($this->admin));
        $this->assertEquals(1000, $activity->properties['old']['amount']);
        $this->assertEquals(2500, $activity->properties['attributes']['amount']);
        $this->assertStringContainsString($subscription->business->name, $activity->properties['label']);
    }

    public function test_passwords_are_never_logged()
    {
        $this->actingAs($this->admin)->post(route('staff.store'), [
            'name' => 'New Staff',
            'email' => 'new@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'roles' => ['Staff'],
        ])->assertSessionHasNoErrors();

        $logged = Activity::where('log_name', 'user')->get()->pluck('properties')->toJson();

        $this->assertStringNotContainsString('password', $logged);
        $this->assertStringContainsString('new@example.com', $logged);
        $this->assertStringContainsString('Staff', $logged); // role assignment
    }

    public function test_role_permission_changes_are_logged()
    {
        $role = Role::findByName('Staff');

        $this->actingAs($this->admin)->put(route('roles.update', $role), [
            'name' => 'Staff',
            'permissions' => [],
        ]);

        $activity = Activity::where('log_name', 'role')->latest('id')->firstOrFail();

        $this->assertSame('updated', $activity->event);
        $this->assertNotEmpty($activity->properties['old']['permissions']);
        $this->assertSame([], $activity->properties['attributes']['permissions']);
    }

    public function test_audit_log_page_lists_and_filters_entries()
    {
        $subscription = Subscription::factory()->create();
        $subscription->delete();

        $this->actingAs($this->admin)
            ->get(route('audit_logs.index', ['type' => 'subscription', 'event' => 'deleted']))
            ->assertInertia(fn (Assert $page) => $page
                ->component('AuditLogs/Index')
                ->has('logs.data', 1)
                ->where('logs.data.0.event', 'deleted')
                ->where('logs.data.0.type', 'subscription'));
    }
}
