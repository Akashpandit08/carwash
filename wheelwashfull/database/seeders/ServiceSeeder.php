<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceCity;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Basic Wash',
                'description' => 'Essential cleaning services',
                'icon' => 'droplet',
                'services' => [
                    [
                        'name' => 'Express Wash',
                        'description' => 'Quick exterior wash and wipe. Perfect for regular cleaning.',
                        'price' => 199,
                        'duration_minutes' => 20,
                        'vehicle_types' => ['car', 'bike'],
                    ],
                    [
                        'name' => 'Premium Wash',
                        'description' => 'Complete exterior and interior cleaning with vacuum and dashboard polish.',
                        'price' => 399,
                        'duration_minutes' => 45,
                        'vehicle_types' => ['car', 'suv'],
                    ],
                ],
            ],
            [
                'name' => 'Deep Cleaning',
                'description' => 'Comprehensive cleaning services',
                'icon' => 'stars',
                'services' => [
                    [
                        'name' => 'Interior Deep Clean',
                        'description' => 'Deep cleaning of seats, carpets, dashboard, and AC vents.',
                        'price' => 799,
                        'duration_minutes' => 60,
                        'vehicle_types' => ['car', 'suv'],
                    ],
                    [
                        'name' => 'Complete Detailing',
                        'description' => 'Full car detailing including exterior, interior, and engine cleaning.',
                        'price' => 1499,
                        'duration_minutes' => 120,
                        'vehicle_types' => ['car', 'suv', 'truck'],
                    ],
                ],
            ],
            [
                'name' => 'Specialized Services',
                'description' => 'Special care services',
                'icon' => 'shield-check',
                'services' => [
                    [
                        'name' => 'Ceramic Coating',
                        'description' => 'Professional ceramic coating for long-lasting shine and protection.',
                        'price' => 4999,
                        'duration_minutes' => 180,
                        'vehicle_types' => ['car', 'suv'],
                    ],
                    [
                        'name' => 'Teflon Coating',
                        'description' => 'Teflon coating for paint protection and easy maintenance.',
                        'price' => 2999,
                        'duration_minutes' => 120,
                        'vehicle_types' => ['car', 'suv'],
                    ],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = ServiceCategory::create([
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'icon' => $categoryData['icon'],
                'is_active' => true,
            ]);

            foreach ($categoryData['services'] as $serviceData) {
                Service::updateOrCreate(['name' => $serviceData['name']], [
                    'category_id' => $category->id,
                    'name' => $serviceData['name'],
                    'description' => $serviceData['description'],
                    'price' => $serviceData['price'],
                    'duration_minutes' => $serviceData['duration_minutes'],
                    'vehicle_types' => $serviceData['vehicle_types'],
                    'is_active' => true,
                ]);
            }
        }

        $requiredServices = [
            ['Quick Wash', 'Fast exterior cleaning for daily shine.', 249, 25, ['car', 'bike'], 'https://images.unsplash.com/photo-1607860108855-64acf2078ed9?auto=format&fit=crop&w=900&q=85'],
            ['Premium Wash', 'Foam wash, vacuum, tyre polish, and dashboard wipe.', 499, 50, ['car', 'suv'], 'https://images.unsplash.com/photo-1605164599901-c8d2c9243025?auto=format&fit=crop&w=900&q=85'],
            ['Deep Interior Cleaning', 'Vacuum, shampoo, AC vent, dashboard, and glass cleaning.', 899, 75, ['car', 'suv'], 'https://images.unsplash.com/photo-1600320254374-ce2d293c324e?auto=format&fit=crop&w=900&q=85'],
            ['Bike Wash', 'Two-wheeler foam wash and chain-safe detailing.', 149, 20, ['bike'], 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=900&q=85'],
            ['SUV Wash', 'Large vehicle premium exterior and interior cleaning.', 699, 65, ['suv'], 'https://images.unsplash.com/photo-1619767886558-efdc259cde1a?auto=format&fit=crop&w=900&q=85'],
        ];

        $defaultCategory = ServiceCategory::firstOrCreate(
            ['name' => 'Doorstep Wash'],
            ['description' => 'Doorstep vehicle wash services', 'icon' => 'car', 'is_active' => true]
        );

        foreach ($requiredServices as [$name, $description, $price, $duration, $vehicleTypes, $image]) {
            Service::updateOrCreate(['name' => $name], [
                'category_id' => $defaultCategory->id,
                'description' => $description,
                'price' => $price,
                'duration_minutes' => $duration,
                'vehicle_types' => $vehicleTypes,
                'image' => $image,
                'is_active' => true,
            ]);
        }

        $cityCategory = ServiceCategory::firstOrCreate(
            ['name' => 'Monthly Wash Services'],
            ['description' => 'City-wise customer wash services', 'icon' => 'sparkles', 'is_active' => true]
        );

        $cityServices = [
            'firozabad' => [
                ['Basic Exterior Wash', 149, 30],
                ['Interior + Exterior Wash', 299, 60],
                ['Foam Wash', 499, 60],
            ],
            'agra' => [
                ['Basic Exterior Wash', 199, 30],
                ['Interior + Exterior Wash', 399, 60],
                ['Foam Wash', 699, 60],
            ],
        ];

        foreach ($cityServices as $citySlug => $services) {
            $city = ServiceCity::where('slug', $citySlug)->first();
            if (! $city) {
                continue;
            }

            foreach ($services as [$name, $price, $duration]) {
                Service::updateOrCreate(
                    ['name' => $name, 'service_city_id' => $city->id],
                    [
                        'category_id' => $cityCategory->id,
                        'service_city_id' => $city->id,
                        'service_zone_id' => null,
                        'service_area' => $city->name,
                        'is_global' => false,
                        'description' => "{$name} for {$city->name}.",
                        'price' => $price,
                        'duration_minutes' => $duration,
                        'vehicle_types' => ['car', 'suv'],
                        'is_active' => true,
                        'status' => 'active',
                        'sort_order' => $duration,
                    ]
                );
            }
        }

        $this->command->info('Services seeded successfully!');
    }
}
