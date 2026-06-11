<?php

namespace Database\Seeders;

use App\Constants\UserRole;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Database\Seeder;

class WorkerUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['mobile_number' => '7777777777'],
            ['name' => 'Ravi Worker', 'email' => 'worker@wheelwash.local', 'password' => 'password', 'role' => UserRole::WORKER]
        );

        WorkerProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['skills' => ['foam_wash', 'detailing'], 'service_area' => 'Agra', 'current_status' => 'available']
        );
    }
}
