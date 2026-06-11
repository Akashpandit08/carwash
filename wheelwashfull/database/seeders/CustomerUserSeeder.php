<?php

namespace Database\Seeders;

use App\Constants\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['mobile_number' => '9876543210'],
            ['name' => 'Akash Sharma', 'email' => 'customer@wheelwash.local', 'password' => 'password', 'role' => UserRole::CUSTOMER]
        );
    }
}
