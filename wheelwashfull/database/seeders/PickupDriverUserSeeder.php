<?php

namespace Database\Seeders;

use App\Constants\UserRole;
use App\Models\PickupDriverProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class PickupDriverUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['mobile_number' => '6666666666'],
            ['name' => 'Manoj Driver', 'email' => 'driver@wheelwash.local', 'password' => 'password', 'role' => UserRole::PICKUP_DRIVER]
        );

        PickupDriverProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['vehicle_type' => 'tow_van', 'license_number' => 'UP80DRV001', 'service_area' => 'Agra', 'current_status' => 'available']
        );
    }
}
