<?php

namespace Database\Seeders;

use App\Constants\UserRole;
use App\Models\PartnerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartnerUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $user = User::updateOrCreate(
                ['mobile_number' => '8888888888'],
                [
                    'name' => 'Prime Wash Partner',
                    'email' => 'partner@wheelwash.local',
                    'password' => '12345678',
                    'role' => UserRole::PARTNER,
                ]
            );

            PartnerProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => 'Prime Wash Center',
                    'address' => 'Dayal Bagh, Agra',
                    'service_area' => 'Agra',
                    'current_status' => 'active',
                ]
            );
        });
    }
}
