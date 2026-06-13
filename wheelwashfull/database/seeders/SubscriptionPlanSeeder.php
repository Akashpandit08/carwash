<?php

namespace Database\Seeders;

use App\Models\ServiceCity;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            'firozabad' => [
                ['Basic Monthly', 499, 4, 0, 0, 1, false, false, false, false, true, false, 1],
                ['Standard Monthly', 899, 4, 2, 0, 2, true, true, true, false, true, false, 2],
                ['Premium Monthly', 1499, 8, 4, 1, 3, true, true, true, true, true, true, 3],
            ],
            'agra' => [
                ['Basic Monthly', 699, 4, 0, 0, 1, false, false, false, false, true, false, 1],
                ['Standard Monthly', 1199, 4, 2, 0, 2, true, true, true, false, true, false, 2],
                ['Premium Monthly', 1999, 8, 4, 1, 3, true, true, true, true, true, true, 3],
            ],
        ];

        foreach ($plans as $citySlug => $cityPlans) {
            $city = ServiceCity::where('slug', $citySlug)->first();
            if (! $city) {
                continue;
            }

            foreach ($cityPlans as [$name, $price, $exterior, $interior, $foam, $maxPerWeek, $tyre, $dashboard, $vacuum, $priority, $doorstep, $pickupDrop, $sort]) {
                SubscriptionPlan::updateOrCreate(
                    [
                        'slug' => Str::slug($name),
                        'service_city_id' => $city->id,
                        'service_zone_id' => null,
                    ],
                    [
                        'service_area' => $city->name,
                        'is_global' => false,
                        'name' => $name,
                        'description' => "{$name} plan for {$city->name}.",
                        'price' => $price,
                        'duration_days' => 30,
                        'total_washes' => $exterior + $interior + $foam,
                        'exterior_washes' => $exterior,
                        'interior_washes' => $interior,
                        'foam_washes' => $foam,
                        'tyre_polish_included' => $tyre,
                        'dashboard_wipe_included' => $dashboard,
                        'vacuum_included' => $vacuum,
                        'priority_booking' => $priority,
                        'pickup_drop_included' => $pickupDrop,
                        'doorstep_included' => $doorstep,
                        'max_washes_per_week' => $maxPerWeek,
                        'terms' => 'Valid for 30 days from activation. Unused washes do not carry forward.',
                        'status' => 'active',
                        'sort_order' => $sort,
                    ]
                );
            }
        }
    }
}
