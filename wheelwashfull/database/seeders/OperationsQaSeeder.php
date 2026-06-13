<?php

namespace Database\Seeders;

use App\Constants\BookingStatus;
use App\Constants\ServiceMode;
use App\Constants\UserRole;
use App\Constants\WashType;
use App\Models\Address;
use App\Models\Booking;
use App\Models\PartnerProfile;
use App\Models\PickupDriverProfile;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Slot;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkerProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperationsQaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->user('QA Admin', 'qa.admin@wheelwash.test', '9999900000', UserRole::ADMIN);
        $partner = $this->user('QA Partner', 'qa.partner@wheelwash.test', '9999900001', UserRole::PARTNER);
        $worker = $this->user('QA Worker', 'qa.worker@wheelwash.test', '9999900002', UserRole::WORKER);
        $driver = $this->user('QA Pickup Driver', 'qa.driver@wheelwash.test', '9999900003', UserRole::PICKUP_DRIVER);
        $customer = $this->user('QA Customer', 'qa.customer@wheelwash.test', '9999900004', UserRole::CUSTOMER);

        PartnerProfile::updateOrCreate(
            ['user_id' => $partner->id],
            ['business_name' => 'QA Washing Center', 'current_status' => 'active', 'latitude' => 27.1769, 'longitude' => 78.0083]
        );

        WorkerProfile::updateOrCreate(
            ['user_id' => $worker->id],
            ['partner_id' => $partner->id, 'current_status' => 'available', 'service_area' => 'Agra', 'latitude' => 27.1768, 'longitude' => 78.0082]
        );

        PickupDriverProfile::updateOrCreate(
            ['user_id' => $driver->id],
            ['partner_id' => $partner->id, 'current_status' => 'available', 'vehicle_type' => 'bike', 'license_number' => 'QA-DL-001', 'latitude' => 27.1770, 'longitude' => 78.0084]
        );

        $category = ServiceCategory::firstOrCreate(['name' => 'QA Wash Services'], ['is_active' => true]);
        $doorService = $this->service($category->id, 'QA Door-to-door Wash', 499);
        $pickupService = $this->service($category->id, 'QA Pickup/drop Wash', 699);
        $driveInService = $this->service($category->id, 'QA Drive-in Wash', 399);

        $vehicle = Vehicle::updateOrCreate(
            ['registration_number' => 'QA01WW0001'],
            ['user_id' => $customer->id, 'vehicle_type' => 'car', 'brand' => 'Hyundai', 'model' => 'i20', 'color' => 'White']
        );

        $address = Address::updateOrCreate(
            ['user_id' => $customer->id, 'type' => 'qa_home'],
            [
                'full_address' => 'QA Home, Dayal Bagh, Agra',
                'city' => 'Agra',
                'state' => 'UP',
                'pincode' => '282005',
                'latitude' => 27.1767,
                'longitude' => 78.0081,
                'is_default' => true,
            ]
        );

        Slot::firstOrCreate(
            ['date' => now()->addDay()->toDateString(), 'start_time' => '10:00:00'],
            ['end_time' => '10:45:00', 'max_bookings' => 5, 'is_active' => true]
        );

        $this->booking('QA-DTD-001', $customer, $vehicle, $doorService, $address, [
            'service_mode' => ServiceMode::DOORSTEP,
            'wash_type' => WashType::DOOR_TO_DOOR,
            'worker_id' => $worker->id,
            'status' => BookingStatus::WORKER_ASSIGNED,
            'total_amount' => 499,
        ]);

        $this->booking('QA-PUD-001', $customer, $vehicle, $pickupService, $address, [
            'service_mode' => ServiceMode::PICKUP_DROP,
            'wash_type' => WashType::PICKUP_WASH,
            'partner_id' => $partner->id,
            'pickup_driver_id' => $driver->id,
            'pickup_address_id' => $address->id,
            'drop_address_id' => $address->id,
            'pickup_fee' => 50,
            'drop_fee' => 50,
            'status' => BookingStatus::PICKUP_DRIVER_ASSIGNED,
            'total_amount' => 699,
        ]);

        $this->booking('QA-DRV-001', $customer, $vehicle, $driveInService, $address, [
            'service_mode' => ServiceMode::PARTNER_CENTER,
            'wash_type' => null,
            'partner_id' => $partner->id,
            'status' => BookingStatus::PARTNER_ASSIGNED,
            'total_amount' => 399,
        ]);
    }

    protected function user(string $name, string $email, string $mobile, string $role): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'mobile_number' => $mobile,
                'password' => Hash::make('password'),
                'role' => $role,
                'status' => 'active',
            ]
        );
    }

    protected function service(int $categoryId, string $name, int $price): Service
    {
        return Service::updateOrCreate(
            ['name' => $name],
            ['category_id' => $categoryId, 'price' => $price, 'duration_minutes' => 45, 'is_active' => true, 'status' => 'active', 'is_global' => true]
        );
    }

    protected function booking(string $number, User $customer, Vehicle $vehicle, Service $service, Address $address, array $extra): Booking
    {
        return Booking::updateOrCreate(
            ['booking_number' => $number],
            array_merge([
                'user_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'service_id' => $service->id,
                'booking_date' => now()->addDay()->toDateString(),
                'slot_time' => '10:00:00',
                'address' => $address->full_address,
                'latitude' => $address->latitude,
                'longitude' => $address->longitude,
                'price' => $extra['total_amount'],
                'discount' => 0,
                'final_price' => $extra['total_amount'],
                'payment_method' => 'cod',
                'payment_status' => 'pending',
            ], $extra)
        );
    }
}
