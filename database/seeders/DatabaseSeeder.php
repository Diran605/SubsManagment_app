<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Service;
use App\Models\ServicePricing;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $services = [
            ['name' => 'POS System', 'desc' => 'Point of Sale management system for retail'],
            ['name' => 'School Management System', 'desc' => 'Complete ERP for schools'],
            ['name' => 'Hospital ERP', 'desc' => 'Management system for clinics and hospitals'],
        ];

        $createdServices = [];
        foreach ($services as $serviceData) {
            $service = Service::create([
                'name' => $serviceData['name'],
                'description' => $serviceData['desc'],
                'created_by' => $admin->id,
            ]);
            $createdServices[] = $service;

            $modes = ['online', 'offline', 'hybrid'];
            foreach ($modes as $mode) {
                ServicePricing::create([
                    'service_id' => $service->id,
                    'mode' => $mode,
                    'base_price' => rand(500, 2000),
                ]);
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

            // Add some subscriptions
            $service = $createdServices[array_rand($createdServices)];
            $mode = ['online', 'offline', 'hybrid'][rand(0, 2)];
            $startDate = now()->subDays(rand(10, 300));
            $endDate = (clone $startDate)->addYear();

            Subscription::create([
                'business_id' => $business->id,
                'service_id' => $service->id,
                'mode' => $mode,
                'amount' => rand(500, 2500),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'created_by' => $admin->id,
            ]);
        }
    }
}
