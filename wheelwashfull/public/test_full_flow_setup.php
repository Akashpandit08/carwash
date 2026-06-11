<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Service;
use App\Models\Vehicle;
use App\Models\Slot;
use App\Models\Booking;
use App\Constants\UserRole;
use App\Constants\BookingStatus;
use App\Constants\ServiceMode;

// 1-5. Users
$users = [
    'admin' => ['mobile' => '3000000001', 'role' => UserRole::ADMIN],
    'customer' => ['mobile' => '3000000002', 'role' => UserRole::CUSTOMER],
    'partner' => ['mobile' => '3000000003', 'role' => UserRole::PARTNER],
    'worker' => ['mobile' => '3000000004', 'role' => UserRole::WORKER],
    'driver' => ['mobile' => '3000000005', 'role' => UserRole::PICKUP_DRIVER],
];

$createdUsers = [];
foreach ($users as $key => $data) {
    $u = User::firstOrCreate(['mobile_number' => $data['mobile']], ['name' => ucfirst($key)]);
    $u->forceFill(['role' => $data['role']])->save();
    $u->update(['otp' => '123456', 'otp_expires_at' => now()->addMinutes(60)]);
    $createdUsers[$key] = $u;
}

// 6. Service
$category = \App\Models\ServiceCategory::firstOrCreate(['name' => 'Wash Category', 'is_active' => true]);

$service = Service::firstOrCreate(
    ['name' => 'Premium Car Wash'],
    [
        'category_id' => $category->id,
        'price' => 499.00,
        'duration_minutes' => 60,
        'is_active' => true,
        'vehicle_types' => ['SUV', 'Sedan']
    ]
);

// 7. Vehicle
$vehicle = Vehicle::firstOrCreate(
    ['registration_number' => 'UP80AB1234'],
    [
        'user_id' => $createdUsers['customer']->id,
        'brand' => 'Hyundai',
        'model' => 'Creta',
        'type' => 'SUV'
    ]
);

// 8. Slot
$slot = Slot::firstOrCreate(
    ['date' => now()->toDateString(), 'start_time' => '10:00:00'],
    [
        'end_time' => '12:00:00',
        'max_bookings' => 5,
        'is_active' => true
    ]
);

// 9A. Garage Booking (Pickup Drop)
$bookingGarage = Booking::create([
    'booking_number' => 'GARAGE-' . time(),
    'user_id' => $createdUsers['customer']->id,
    'vehicle_id' => $vehicle->id,
    'service_id' => $service->id,
    'service_mode' => ServiceMode::PICKUP_DROP,
    'booking_date' => now()->toDateString(),
    'slot_time' => '10:00:00',
    'address' => 'Test Address',
    'price' => 499.00,
    'total_amount' => 499.00,
    'final_price' => 499.00,
    'status' => BookingStatus::PENDING,
    'pickup_driver_id' => null,
    'partner_id' => null,
    'worker_id' => null
]);

// 9B. Doorstep Booking
$bookingDoorstep = Booking::create([
    'booking_number' => 'DOORSTEP-' . time(),
    'user_id' => $createdUsers['customer']->id,
    'vehicle_id' => $vehicle->id,
    'service_id' => $service->id,
    'service_mode' => ServiceMode::DOORSTEP,
    'booking_date' => now()->toDateString(),
    'slot_time' => '10:00:00',
    'address' => 'Test Address',
    'price' => 499.00,
    'total_amount' => 499.00,
    'final_price' => 499.00,
    'status' => BookingStatus::PENDING,
    'pickup_driver_id' => null,
    'partner_id' => null,
    'worker_id' => null
]);

// Print Output format required by Node script
$output = [
    'admin_phone' => '3000000001',
    'customer_phone' => '3000000002',
    'partner_phone' => '3000000003',
    'worker_phone' => '3000000004',
    'pickup_driver_phone' => '3000000005',
    'garage_booking_id' => $bookingGarage->id,
    'doorstep_booking_id' => $bookingDoorstep->id,
    'admin_id' => $createdUsers['admin']->id,
    'partner_id' => $createdUsers['partner']->id,
    'worker_id' => $createdUsers['worker']->id,
    'driver_id' => $createdUsers['driver']->id,
];

echo json_encode($output);
