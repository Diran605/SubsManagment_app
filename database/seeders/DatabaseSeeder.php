<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with roles and some demo data.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $admin->assignRole('Admin');

        $catalogue = [
            [
                'name' => 'POS System',
                'description' => 'Point of Sale management system for retail',
                'category' => 'standard',
                'modules' => ['Inventory', 'Sales', 'Reporting'],
                'packages' => [
                    ['name' => 'Basic', 'price' => 15000, 'pricing_model' => 'flat', 'payer_type' => 'school', 'modules' => 2],
                    ['name' => 'Pro', 'price' => 30000, 'pricing_model' => 'flat', 'payer_type' => 'school', 'modules' => 3],
                ],
            ],
            [
                'name' => 'School Management System',
                'description' => 'Complete ERP for schools',
                'category' => 'education',
                'modules' => ['Admissions', 'Grades', 'Fees'],
                'packages' => [
                    ['name' => 'School Licence', 'price' => 2000, 'pricing_model' => 'per_student', 'payer_type' => 'school', 'modules' => 3],
                    ['name' => 'Student MoMo', 'price' => 1500, 'pricing_model' => 'per_student', 'payer_type' => 'student_momo', 'modules' => 2],
                ],
            ],
        ];

        $packages = collect();

        foreach ($catalogue as $item) {
            $service = Service::create([
                'name' => $item['name'],
                'description' => $item['description'],
                'category' => $item['category'],
                'created_by' => $admin->id,
            ]);

            $modules = collect($item['modules'])
                ->map(fn ($name) => $service->modules()->create(['name' => $name]));

            foreach ($item['packages'] as $pkg) {
                $package = $service->packages()->create([
                    'name' => $pkg['name'],
                    'price' => $pkg['price'],
                    'pricing_model' => $pkg['pricing_model'],
                    'payer_type' => $pkg['payer_type'],
                    'supported_modes' => ['online', 'offline', 'hybrid'],
                ]);
                $package->modules()->sync($modules->take($pkg['modules'])->pluck('id'));
                $packages->push($package);
            }
        }

        for ($i = 1; $i <= 10; $i++) {
            $business = Business::create([
                'name' => "Business $i",
                'contact_person' => "Person $i",
                'phone' => "123456789$i",
                'email' => "contact$i@business.com",
                'address' => "Address $i",
                'created_by' => $admin->id,
            ]);

            $package = $packages->random();
            $startDate = now()->subDays(rand(10, 300))->startOfDay();
            $students = $package->pricing_model === 'per_student' ? rand(200, 1200) : null;

            $subscription = Subscription::create([
                'business_id' => $business->id,
                'service_id' => $package->service_id,
                'package_id' => $package->id,
                'mode' => ['online', 'offline', 'hybrid'][rand(0, 2)],
                'amount' => $students ? $students * $package->price : $package->price * 12,
                'student_count' => $students,
                'start_date' => $startDate,
                'end_date' => $startDate->addYear()->subDay(),
                'created_by' => $admin->id,
            ]);
            $subscription->modules()->sync($package->modules->pluck('id'));
        }
    }
}
