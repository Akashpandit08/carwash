<?php

namespace Database\Factories;

use App\Models\CustomerSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerSubscription>
 */
class CustomerSubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'subscription_plan_id' => \App\Models\SubscriptionPlan::factory(),
            'status' => 'active',
            'remaining_washes' => 4,
            'used_washes' => 0,
            'total_washes' => 4,
            'exterior_remaining' => 4,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(30),
        ];
    }
}
