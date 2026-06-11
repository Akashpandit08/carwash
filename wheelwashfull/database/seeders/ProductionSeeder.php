<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. First Admin User
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'admin@wheelwash.com',
                'mobile_number' => '9999999999',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]);
            $this->command->info('Admin user created (admin@wheelwash.com / password)');
        }

        // 2. Sample Partner User
        $partner = User::where('role', 'partner')->first();
        if (!$partner) {
            User::create([
                'name' => 'Sample Partner',
                'email' => 'partner@wheelwash.com',
                'mobile_number' => '8888888888',
                'password' => Hash::make('password'),
                'role' => 'partner',
                'is_active' => true,
            ]);
            $this->command->info('Sample Partner user created (partner@wheelwash.com / password)');
        }

        // 3. Sample Category & Service
        $category = ServiceCategory::first();
        if (!$category) {
            $category = ServiceCategory::create([
                'name' => 'Basic Wash',
                'description' => 'Standard cleaning services',
                'is_active' => true,
            ]);
            $this->command->info('Sample Category created');
        }

        $service = Service::first();
        if (!$service) {
            Service::create([
                'service_category_id' => $category->id,
                'name' => 'Exterior Wash & Wax',
                'description' => 'Complete exterior wash with premium wax coating.',
                'price' => 499.00,
                'duration_minutes' => 45,
                'is_active' => true,
            ]);
            $this->command->info('Sample Service created');
        }

        // 4. Sample Slots
        if (Slot::count() === 0) {
            $times = [
                '09:00:00', '10:00:00', '11:00:00', '12:00:00',
                '14:00:00', '15:00:00', '16:00:00', '17:00:00'
            ];
            foreach ($times as $time) {
                Slot::create([
                    'time' => $time,
                    'is_active' => true,
                ]);
            }
            $this->command->info('Sample Slots created');
        }

        // 5. Sample Coupon
        $coupon = Coupon::first();
        if (!$coupon) {
            Coupon::create([
                'code' => 'WELCOME50',
                'description' => 'Flat ₹50 off on first booking',
                'discount_type' => 'fixed',
                'discount_value' => 50,
                'min_order_amount' => 200,
                'is_active' => true,
            ]);
            $this->command->info('Sample Coupon created (WELCOME50)');
        }
    }
}
