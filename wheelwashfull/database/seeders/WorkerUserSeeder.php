<?php

namespace Database\Seeders;

use App\Constants\UserRole;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkerUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $user = User::updateOrCreate(
                ['mobile_number' => '7777777777'],
                [
                    'name' => 'Ravi Worker',
                    'email' => 'worker@wheelwash.local',
                    'password' => '12345678',
                    'role' => UserRole::WORKER,
                ]
            );

            WorkerProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'skills' => ['foam_wash', 'detailing'],
                    'service_area' => 'Agra',
                    'current_status' => 'available',
                ]
            );
        });
    }
}
