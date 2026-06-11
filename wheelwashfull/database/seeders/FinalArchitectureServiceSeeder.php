<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class FinalArchitectureServiceSeeder extends Seeder
{
    public function run(): void
    {
        $category = ServiceCategory::firstOrCreate(
            ['name' => 'Car Wash'],
            ['description' => 'Doorstep and center car wash services', 'is_active' => true]
        );

        foreach ([
            ['Premium Foam Wash', 499, 45],
            ['Exterior Wash', 299, 30],
            ['Interior Cleaning', 699, 60],
            ['Full Detailing', 1299, 120],
        ] as [$name, $price, $duration]) {
            Service::updateOrCreate(
                ['name' => $name],
                [
                    'category_id' => $category->id,
                    'description' => "{$name} by trained WheelWash professionals.",
                    'price' => $price,
                    'duration_minutes' => $duration,
                    'is_active' => true,
                ]
            );
        }
    }
}
