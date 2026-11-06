<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state: a one-year subscription starting today.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->startOfDay();

        return [
            'business_id' => Business::factory(),
            'service_id' => Service::factory(),
            'mode' => 'online',
            'amount' => 120000,
            'start_date' => $start,
            'end_date' => $start->addYear()->subDay(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * A one-year subscription whose last day is `$days` days from today.
     */
    public function endingInDays(int $days): static
    {
        return $this->state(fn () => [
            'start_date' => now()->startOfDay()->addDays($days)->subYear()->addDay(),
            'end_date' => now()->startOfDay()->addDays($days),
        ]);
    }
}
