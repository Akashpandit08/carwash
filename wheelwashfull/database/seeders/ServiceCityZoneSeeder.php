<?php

namespace Database\Seeders;

use App\Models\ServiceCity;
use App\Models\ServiceZone;
use Illuminate\Database\Seeder;

class ServiceCityZoneSeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Firozabad', 'slug' => 'firozabad', 'state' => 'Uttar Pradesh', 'sort_order' => 1],
            ['name' => 'Agra', 'slug' => 'agra', 'state' => 'Uttar Pradesh', 'sort_order' => 2],
        ];

        foreach ($cities as $cityData) {
            $city = ServiceCity::updateOrCreate(
                ['slug' => $cityData['slug']],
                $cityData + ['status' => 'active']
            );

            ServiceZone::updateOrCreate(
                ['service_city_id' => $city->id, 'slug' => $cityData['slug'].'-central'],
                [
                    'name' => $cityData['name'].' Central',
                    'status' => 'active',
                    'sort_order' => 1,
                ]
            );
        }
    }
}
