<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME50',
                'description' => '50% off on first booking (max ₹200 discount)',
                'discount_type' => 'percentage',
                'discount_value' => 50,
                'min_order_amount' => 500,
                'max_discount' => 200,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonths(3),
                'usage_limit' => 100,
                'is_active' => true,
            ],
            [
                'code' => 'FLAT100',
                'description' => 'Flat ₹100 off on orders above ₹500',
                'discount_type' => 'fixed',
                'discount_value' => 100,
                'min_order_amount' => 500,
                'max_discount' => null,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonth(),
                'usage_limit' => 50,
                'is_active' => true,
            ],
            [
                'code' => 'SAVE20',
                'description' => '20% off on all services (max ₹150 discount)',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'min_order_amount' => 300,
                'max_discount' => 150,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonths(6),
                'usage_limit' => null, // Unlimited usage
                'is_active' => true,
            ],
            [
                'code' => 'WEEKEND25',
                'description' => '25% off on weekend bookings',
                'discount_type' => 'percentage',
                'discount_value' => 25,
                'min_order_amount' => 400,
                'max_discount' => 180,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addWeeks(4),
                'usage_limit' => 200,
                'is_active' => true,
            ],
            [
                'code' => 'PREMIUM50',
                'description' => 'Flat ₹50 off on any order',
                'discount_type' => 'fixed',
                'discount_value' => 50,
                'min_order_amount' => 200,
                'max_discount' => null,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonths(2),
                'usage_limit' => 150,
                'is_active' => true,
            ],
            [
                'code' => 'EXPIRED10',
                'description' => 'Expired coupon (for testing)',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'min_order_amount' => 100,
                'max_discount' => 50,
                'valid_from' => Carbon::now()->subMonths(2),
                'valid_until' => Carbon::now()->subMonth(),
                'usage_limit' => 100,
                'is_active' => true,
            ],
            [
                'code' => 'INACTIVE30',
                'description' => 'Inactive coupon (for testing)',
                'discount_type' => 'percentage',
                'discount_value' => 30,
                'min_order_amount' => 250,
                'max_discount' => 100,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonths(3),
                'usage_limit' => 100,
                'is_active' => false,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(['code' => $coupon['code']], $coupon);
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

        $this->command->info('Coupons seeded successfully!');
    }
}
