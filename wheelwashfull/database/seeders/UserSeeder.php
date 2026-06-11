<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users for each role
        $users = [
            [
                'name' => 'Customer User',
                'mobile_number' => '1111111111',
                'role' => 'customer',
                'email' => 'customer@washmate.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            ],
            [
                'name' => 'Partner User',
                'mobile_number' => '2222222222',
                'role' => 'partner',
                'email' => 'partner@washmate.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            ],
            [
                'name' => 'Admin User',
                'mobile_number' => '3333333333',
                'role' => 'admin',
                'email' => 'admin@washmate.com',
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['mobile_number' => $userData['mobile_number']],
                $userData
            );
        }

        $this->command->info('Test users created successfully!');
        $this->command->info('Customer: 1111111111');
        $this->command->info('Partner: 2222222222');
        $this->command->info('Admin: 3333333333');
    }
}
