<?php

namespace Database\Seeders;

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
        // Seed test users with different roles
        $this->call([
            ServiceCityZoneSeeder::class,
            AdminUserSeeder::class,
            CustomerUserSeeder::class,
            PartnerUserSeeder::class,
            WorkerUserSeeder::class,
            PickupDriverUserSeeder::class,
            UserSeeder::class,
            ServiceSeeder::class,
            SubscriptionPlanSeeder::class,
            FinalArchitectureServiceSeeder::class,
            CouponSeeder::class,
            SlotSeeder::class,
            AppContentImageSeeder::class,
            DynamicAppContentSeeder::class,
            OperationsQaSeeder::class,
        ]);
    }
}
