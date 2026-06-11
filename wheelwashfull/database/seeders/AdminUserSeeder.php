<?php

namespace Database\Seeders;

use App\Constants\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['mobile_number' => '9999999999'],
            ['name' => 'WheelWash Admin', 'email' => 'admin@wheelwash.local', 'password' => 'password', 'role' => UserRole::ADMIN]
        );
    }
}
