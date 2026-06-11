<?php

namespace Database\Seeders;

use App\Models\AppBanner;
use App\Models\Banner;
use App\Models\Service;
use Illuminate\Database\Seeder;

class AppContentImageSeeder extends Seeder
{
    public function run(): void
    {
        $serviceImages = [
            'Express Wash' => 'https://images.unsplash.com/photo-1607860108855-64acf2078ed9?auto=format&fit=crop&w=900&q=85',
            'Premium Wash' => 'https://images.unsplash.com/photo-1605164599901-c8d2c9243025?auto=format&fit=crop&w=900&q=85',
            'Interior Deep Clean' => 'https://images.unsplash.com/photo-1600320254374-ce2d293c324e?auto=format&fit=crop&w=900&q=85',
            'Complete Detailing' => 'https://images.unsplash.com/photo-1619405399517-d7fce0f13302?auto=format&fit=crop&w=900&q=85',
            'Ceramic Coating' => 'https://images.unsplash.com/photo-1607860108855-64acf2078ed9?auto=format&fit=crop&w=900&q=85',
            'Teflon Coating' => 'https://images.unsplash.com/photo-1619405399517-d7fce0f13302?auto=format&fit=crop&w=900&q=85',
        ];

        foreach ($serviceImages as $name => $image) {
            Service::where('name', $name)->update(['image' => $image]);
        }

        AppBanner::updateOrCreate(
            ['position' => 'home_top', 'sort_order' => 1],
            [
                'title' => 'Book doorstep car wash in minutes',
                'subtitle' => 'Professional care for your car, at your doorstep.',
                'image' => '',
                'type' => 'screen',
                'redirect_screen' => '/services',
                'redirect_value' => null,
                'is_active' => true,
            ]
        );

        AppBanner::updateOrCreate(
            ['position' => 'services_top', 'sort_order' => 1],
            [
                'title' => 'Detailing that shines',
                'subtitle' => 'Explore premium care plans for every vehicle.',
                'image' => 'https://images.unsplash.com/photo-1619405399517-d7fce0f13302?auto=format&fit=crop&w=1200&q=85',
                'type' => 'screen',
                'redirect_screen' => '/services',
                'redirect_value' => null,
                'is_active' => true,
            ]
        );

        $banners = [
            [
                'title' => 'Welcome to WheelWash',
                'subtitle' => 'Use FIRSTWASH and save on your first doorstep wash.',
                'image' => 'https://images.unsplash.com/photo-1607860108855-64acf2078ed9?auto=format&fit=crop&w=1200&q=85',
                'redirect_type' => 'offers',
                'redirect_value' => null,
                'user_type' => 'customer',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Premium wash at your doorstep',
                'subtitle' => 'Foam wash, vacuum cleaning, and dashboard polish in one visit.',
                'image' => 'https://images.unsplash.com/photo-1605164599901-c8d2c9243025?auto=format&fit=crop&w=1200&q=85',
                'redirect_type' => 'services',
                'redirect_value' => null,
                'user_type' => 'customer',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Weekend coupon live',
                'subtitle' => 'Use WEEKEND10 for extra savings this weekend.',
                'image' => 'https://images.unsplash.com/photo-1619405399517-d7fce0f13302?auto=format&fit=crop&w=1200&q=85',
                'redirect_type' => 'offers',
                'redirect_value' => 'WEEKEND10',
                'user_type' => 'customer',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Earn more with WheelWash',
                'subtitle' => 'Accept nearby jobs and track earnings from your partner app.',
                'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=1200&q=85',
                'redirect_type' => 'custom_screen',
                'redirect_value' => '/partner/jobs',
                'user_type' => 'partner',
                'sort_order' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::updateOrCreate(
                ['title' => $banner['title'], 'user_type' => $banner['user_type']],
                $banner
            );
        }

        $this->command->info('App banner and service image URLs seeded.');
    }
}
