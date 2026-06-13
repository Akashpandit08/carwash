<?php

namespace Database\Seeders;

use App\Constants\UserRole;
use App\Models\ServiceCity;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CityAdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->cityAdmin('firozabad', 'Firozabad Admin', '9000000001', 'firozabad.admin@wheelwash.local');
        $this->cityAdmin('agra', 'Agra Admin', '9000000002', 'agra.admin@wheelwash.local');
    }

    private function cityAdmin(string $citySlug, string $name, string $mobile, string $email): void
    {
        $city = ServiceCity::where('slug', $citySlug)->firstOrFail();

        User::updateOrCreate(
            ['mobile_number' => $mobile],
            [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => UserRole::CITY_ADMIN,
                'status' => 'active',
                'service_city_id' => $city->id,
                'service_zone_id' => null,
            ]
        );
    }
}
