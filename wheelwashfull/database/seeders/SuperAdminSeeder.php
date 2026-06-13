<?php

namespace Database\Seeders;

use App\Constants\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['mobile_number' => '9999999999'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@wheelwash.local',
                'password' => Hash::make('password'),
                'role' => UserRole::SUPER_ADMIN,
                'status' => 'active',
                'service_city_id' => null,
                'service_zone_id' => null,
            ]
        );
    }
}
