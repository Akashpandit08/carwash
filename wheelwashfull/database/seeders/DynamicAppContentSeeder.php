<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Coupon;
use App\Models\Service;
use App\Models\ServiceCategory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DynamicAppContentSeeder extends Seeder
{
    public function run(): void
    {
        $category = ServiceCategory::firstOrCreate(
            ['name' => 'Doorstep Wash'],
            ['description' => 'Doorstep vehicle wash services', 'icon' => 'car', 'is_active' => true]
        );

        foreach ([
            ['Quick Wash', 'Fast exterior cleaning for daily shine.', 249, 25, ['car', 'bike'], 'https://images.unsplash.com/photo-1607860108855-64acf2078ed9?auto=format&fit=crop&w=900&q=85'],
            ['Premium Wash', 'Foam wash, vacuum, tyre polish, and dashboard wipe.', 499, 50, ['car', 'suv'], 'https://images.unsplash.com/photo-1605164599901-c8d2c9243025?auto=format&fit=crop&w=900&q=85'],
            ['Deep Interior Cleaning', 'Vacuum, shampoo, AC vent, dashboard, and glass cleaning.', 899, 75, ['car', 'suv'], 'https://images.unsplash.com/photo-1600320254374-ce2d293c324e?auto=format&fit=crop&w=900&q=85'],
            ['Bike Wash', 'Two-wheeler foam wash and chain-safe detailing.', 149, 20, ['bike'], 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=900&q=85'],
            ['SUV Wash', 'Large vehicle premium exterior and interior cleaning.', 699, 65, ['suv'], 'https://images.unsplash.com/photo-1619767886558-efdc259cde1a?auto=format&fit=crop&w=900&q=85'],
        ] as [$name, $description, $price, $duration, $vehicleTypes, $image]) {
            Service::updateOrCreate(['name' => $name], [
                'category_id' => $category->id,
                'description' => $description,
                'price' => $price,
                'duration_minutes' => $duration,
                'vehicle_types' => $vehicleTypes,
                'image' => $image,
                'is_active' => true,
            ]);
        }

        foreach ([
            ['FIRSTWASH', 'Flat Rs 100 off on your first WheelWash booking', 'fixed', 100, 299, null, 500],
            ['PREMIUM20', '20% off premium wash plans', 'percentage', 20, 399, 200, 500],
            ['WEEKEND10', '10% off on weekend doorstep bookings', 'percentage', 10, 299, 100, 1000],
        ] as [$code, $description, $type, $value, $min, $max, $limit]) {
            Coupon::updateOrCreate(['code' => $code], [
                'description' => $description,
                'discount_type' => $type,
                'discount_value' => $value,
                'min_order_amount' => $min,
                'max_discount' => $max,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonths(6),
                'usage_limit' => $limit,
                'is_active' => true,
            ]);
        }

        foreach ([
            ['Welcome to WheelWash', 'Use FIRSTWASH and save on your first doorstep wash.', 'https://images.unsplash.com/photo-1607860108855-64acf2078ed9?auto=format&fit=crop&w=1200&q=85', 'offers', null, 'customer', 1],
            ['Premium wash at your doorstep', 'Foam wash, vacuum cleaning, and dashboard polish in one visit.', 'https://images.unsplash.com/photo-1605164599901-c8d2c9243025?auto=format&fit=crop&w=1200&q=85', 'services', null, 'customer', 2],
            ['Weekend coupon live', 'Use WEEKEND10 for extra savings this weekend.', 'https://images.unsplash.com/photo-1619405399517-d7fce0f13302?auto=format&fit=crop&w=1200&q=85', 'offers', 'WEEKEND10', 'customer', 3],
            ['Earn more with WheelWash', 'Accept nearby jobs and track earnings from your partner app.', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=1200&q=85', 'custom_screen', '/partner/jobs', 'partner', 1],
        ] as [$title, $subtitle, $image, $redirectType, $redirectValue, $userType, $sort]) {
            Banner::updateOrCreate(['title' => $title, 'user_type' => $userType], [
                'subtitle' => $subtitle,
                'image' => $image,
                'redirect_type' => $redirectType,
                'redirect_value' => $redirectValue,
                'sort_order' => $sort,
                'is_active' => true,
            ]);
        }
    }
}
