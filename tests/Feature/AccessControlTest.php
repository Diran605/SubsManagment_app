<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_public_registration_is_disabled()
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_dashboard_hides_subscription_data_from_users_without_permission()
    {
        Subscription::factory()->create(['amount' => 500000]);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('canViewSubscriptions', false)
                ->where('totalRevenue', 0)
                ->where('expiringSubscriptions', []));
    }

    public function test_dashboard_shows_subscription_data_to_permitted_users()
    {
        Subscription::factory()->create(['amount' => 500000]);

        $this->actingAs($this->userWithRole('Staff'))
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('canViewSubscriptions', true)
                ->where('totalRevenue', fn ($v) => (float) $v === 500000.0));
    }

    public function test_deactivated_users_are_logged_out_on_their_next_request()
    {
        $user = $this->userWithRole('Staff');
        $user->update(['is_active' => false]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_collections_require_the_edit_subscriptions_permission()
    {
        $subscription = $this->studentMoMoSubscription();

        $this->actingAs(User::factory()->create())
            ->post(route('subscriptions.collections.store', $subscription), [
                'amount' => 1000,
                'collection_date' => now()->toDateString(),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('subscription_collections', 0);
    }

    public function test_collections_are_only_accepted_for_student_funded_subscriptions()
    {
        $subscription = Subscription::factory()->create();

        $this->actingAs($this->userWithRole('Staff'))
            ->post(route('subscriptions.collections.store', $subscription), [
                'amount' => 1000,
                'collection_date' => now()->toDateString(),
            ]);

        $this->assertDatabaseCount('subscription_collections', 0);

        $this->post(route('subscriptions.collections.store', $this->studentMoMoSubscription()), [
            'amount' => 1000,
            'collection_date' => now()->toDateString(),
        ]);

        $this->assertDatabaseCount('subscription_collections', 1);
    }

    public function test_admins_cannot_deactivate_themselves()
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)->patch(route('staff.toggle', $admin));

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admins_cannot_remove_their_own_admin_role()
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->put(route('staff.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'is_active' => true,
                'roles' => ['Staff'],
            ])
            ->assertSessionHasErrors('roles');

        $this->assertTrue($admin->fresh()->hasRole('Admin'));
    }

    public function test_the_admin_role_cannot_be_renamed()
    {
        $admin = $this->userWithRole('Admin');
        $role = Role::findByName('Admin');

        $this->actingAs($admin)
            ->put(route('roles.update', $role), ['name' => 'Boss', 'permissions' => []])
            ->assertSessionHasErrors('name');

        $this->assertSame('Admin', $role->fresh()->name);
    }

    public function test_success_messages_are_shown_as_toasts()
    {
        $this->actingAs($this->userWithRole('Admin'))
            ->followingRedirects()
            ->post(route('businesses.store'), ['name' => 'Acme'])
            ->assertInertia(fn (Assert $page) => $page
                ->hasFlash('toast.type', 'success')
                ->hasFlash('toast.message', 'Business created successfully.'));
    }

    private function studentMoMoSubscription(): Subscription
    {
        $service = Service::factory()->create(['category' => 'education']);
        $package = Package::create([
            'service_id' => $service->id,
            'name' => 'Student MoMo',
            'price' => 1500,
            'pricing_model' => 'per_student',
            'payer_type' => 'student_momo',
            'supported_modes' => ['online'],
        ]);

        return Subscription::factory()->create([
            'service_id' => $service->id,
            'package_id' => $package->id,
            'student_count' => 100,
            'amount' => 150000,
        ]);
    }
}
