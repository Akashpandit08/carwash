<?php

namespace Tests\Feature;

use App\Constants\BookingStatus;
use App\Constants\MediaType;
use App\Constants\ServiceMode;
use App\Constants\UserRole;
use App\Models\Booking;
use App\Models\Address;
use App\Models\PartnerProfile;
use App\Models\PickupDriverProfile;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Slot;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkerProfile;
use App\Constants\WashType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FinalArchitectureFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected User $partner;
    protected User $worker;
    protected User $driver;
    protected User $deliveryDriver;
    protected Service $service;
    protected Vehicle $vehicle;
    protected Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->user(UserRole::ADMIN, '9999999999');
        $this->customer = $this->user(UserRole::CUSTOMER, '9876543210');
        $this->partner = $this->user(UserRole::PARTNER, '8888888888');
        $this->worker = $this->user(UserRole::WORKER, '7777777777');
        $this->driver = $this->user(UserRole::PICKUP_DRIVER, '6666666666');
        $this->deliveryDriver = $this->user(UserRole::PICKUP_DRIVER, '6666666667');

        $category = ServiceCategory::create(['name' => 'Car Wash', 'is_active' => true]);
        $this->service = Service::create([
            'category_id' => $category->id,
            'name' => 'Premium Foam Wash',
            'price' => 499,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);

        $this->vehicle = Vehicle::create([
            'user_id' => $this->customer->id,
            'vehicle_type' => 'car',
            'brand' => 'Hyundai',
            'model' => 'i20',
            'registration_number' => 'UP80AB1234',
            'color' => 'White',
        ]);

        $this->address = Address::create([
            'user_id' => $this->customer->id,
            'type' => 'home',
            'full_address' => 'Home, Dayal Bagh, Agra',
            'city' => 'Agra',
            'state' => 'UP',
            'pincode' => '282005',
            'latitude' => 27.1767,
            'longitude' => 78.0081,
            'is_default' => true,
        ]);

        Slot::create([
            'date' => now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:45:00',
            'max_bookings' => 5,
            'is_active' => true,
        ]);

        WorkerProfile::create([
            'user_id' => $this->worker->id,
            'current_status' => 'available',
            'latitude' => 27.1768,
            'longitude' => 78.0082,
        ]);

        PartnerProfile::create([
            'user_id' => $this->partner->id,
            'business_name' => 'Agra Wash Partner',
            'current_status' => 'active',
            'latitude' => 27.1769,
            'longitude' => 78.0083,
        ]);

        PickupDriverProfile::create([
            'user_id' => $this->driver->id,
            'current_status' => 'available',
            'latitude' => 27.1770,
            'longitude' => 78.0084,
        ]);

        PickupDriverProfile::create([
            'user_id' => $this->deliveryDriver->id,
            'current_status' => 'available',
            'latitude' => 27.1771,
            'longitude' => 78.0085,
        ]);
    }

    public function test_customer_creates_doorstep_booking_admin_assigns_worker_worker_completes_and_payout_is_generated(): void
    {
        $bookingId = $this->createBooking(ServiceMode::DOORSTEP);

        $this->actingAs($this->admin, 'api')
            ->postJson("/api/admin/bookings/{$bookingId}/assign-worker", ['worker_id' => $this->worker->id])
            ->assertOk()
            ->assertJsonPath('data.status', BookingStatus::WORKER_ASSIGNED);

        $this->actingAs($this->worker, 'api')
            ->postJson("/api/operations/worker/jobs/{$bookingId}/status", ['status' => BookingStatus::WORKER_ON_THE_WAY])
            ->assertOk();

        $this->actingAs($this->worker, 'api')
            ->postJson("/api/operations/worker/jobs/{$bookingId}/status", ['status' => BookingStatus::SERVICE_STARTED])
            ->assertOk();

        $this->uploadMedia('worker', $bookingId, MediaType::BEFORE_IMAGE);
        $this->uploadMedia('worker', $bookingId, MediaType::AFTER_IMAGE);

        $this->actingAs($this->worker, 'api')
            ->postJson("/api/operations/worker/jobs/{$bookingId}/status", ['status' => BookingStatus::SERVICE_COMPLETED])
            ->assertOk();

        $this->actingAs($this->worker, 'api')
            ->postJson("/api/operations/worker/jobs/{$bookingId}/status", ['status' => BookingStatus::COMPLETED])
            ->assertOk();

        $this->assertDatabaseHas('payouts', ['booking_id' => $bookingId, 'user_id' => $this->worker->id]);
    }

    public function test_customer_creates_partner_center_booking_admin_assigns_partner_and_partner_completes_service(): void
    {
        $bookingId = $this->createBooking(ServiceMode::PARTNER_CENTER);

        $this->actingAs($this->admin, 'api')
            ->postJson("/api/admin/bookings/{$bookingId}/assign-partner", ['partner_id' => $this->partner->id])
            ->assertOk()
            ->assertJsonPath('data.status', BookingStatus::PARTNER_ASSIGNED);

        $this->actingAs($this->partner, 'api')
            ->postJson("/api/operations/partner/jobs/{$bookingId}/status", ['status' => BookingStatus::SERVICE_STARTED])
            ->assertOk();

        $this->uploadMedia('partner', $bookingId, MediaType::PARTNER_SERVICE_PROOF);

        $this->actingAs($this->partner, 'api')
            ->postJson("/api/operations/partner/jobs/{$bookingId}/status", ['status' => BookingStatus::SERVICE_COMPLETED])
            ->assertOk();

        $this->actingAs($this->admin, 'api')
            ->postJson("/api/admin/bookings/{$bookingId}/status", ['status' => BookingStatus::COMPLETED])
            ->assertOk();

        $this->assertDatabaseHas('payouts', ['booking_id' => $bookingId, 'user_id' => $this->partner->id]);
    }

    public function test_pickup_drop_driver_partner_flow_and_live_tracking(): void
    {
        $bookingId = $this->createBooking(ServiceMode::PICKUP_DROP);

        $this->actingAs($this->admin, 'api')
            ->postJson("/api/admin/bookings/{$bookingId}/assign-pickup-driver", ['pickup_driver_id' => $this->driver->id])
            ->assertOk();

        $this->actingAs($this->admin, 'api')
            ->postJson("/api/admin/bookings/{$bookingId}/assign-partner", ['partner_id' => $this->partner->id])
            ->assertOk();

        $this->actingAs($this->driver, 'api')
            ->postJson('/api/operations/location/update', [
                'booking_id' => $bookingId,
                'latitude' => 27.1767,
                'longitude' => 78.0081,
            ])
            ->assertCreated();

        $this->actingAs($this->customer, 'api')
            ->getJson("/api/customer/bookings/{$bookingId}/tracking")
            ->assertOk()
            ->assertJsonPath('data.0.role', UserRole::PICKUP_DRIVER);

        $this->driverStatus($bookingId, BookingStatus::DRIVER_ON_THE_WAY);
        $this->uploadMedia('driver', $bookingId, MediaType::PICKUP_PROOF);
        $this->driverStatus($bookingId, BookingStatus::CAR_PICKED_UP);
        $this->driverStatus($bookingId, BookingStatus::REACHED_PARTNER);

        $this->actingAs($this->partner, 'api')
            ->postJson("/api/operations/partner/jobs/{$bookingId}/status", ['status' => BookingStatus::SERVICE_STARTED])
            ->assertOk();

        $this->uploadMedia('partner', $bookingId, MediaType::PARTNER_SERVICE_PROOF);
        $this->actingAs($this->partner, 'api')
            ->postJson("/api/operations/partner/jobs/{$bookingId}/status", ['status' => BookingStatus::SERVICE_COMPLETED])
            ->assertOk();

        $this->driverStatus($bookingId, BookingStatus::OUT_FOR_DELIVERY);
        $this->uploadMedia('driver', $bookingId, MediaType::DELIVERY_PROOF);
        $this->driverStatus($bookingId, BookingStatus::DELIVERED);

        $this->actingAs($this->admin, 'api')
            ->postJson("/api/admin/bookings/{$bookingId}/status", ['status' => BookingStatus::COMPLETED])
            ->assertOk();

        $this->assertDatabaseHas('payouts', ['booking_id' => $bookingId, 'user_id' => $this->partner->id]);
        $this->assertDatabaseHas('payouts', ['booking_id' => $bookingId, 'user_id' => $this->driver->id]);
    }

    public function test_wash_type_slots_are_geo_filtered_and_pickup_wash_auto_assigns_all_resources(): void
    {
        $date = now()->addDay()->toDateString();

        $this->actingAs($this->customer, 'api')
            ->getJson('/api/customer/available-slots?' . http_build_query([
                'service_id' => $this->service->id,
                'wash_type' => WashType::DOOR_TO_DOOR,
                'latitude' => 27.1767,
                'longitude' => 78.0081,
                'date' => $date,
            ]))
            ->assertOk()
            ->assertJsonPath('slots.0.available', true)
            ->assertJsonPath('slots.0.worker_available', true);

        $this->actingAs($this->customer, 'api')
            ->getJson('/api/customer/available-slots?' . http_build_query([
                'service_id' => $this->service->id,
                'wash_type' => WashType::PICKUP_WASH,
                'latitude' => 27.1767,
                'longitude' => 78.0081,
                'date' => $date,
            ]))
            ->assertOk()
            ->assertJsonPath('slots.0.available', true)
            ->assertJsonPath('slots.0.pickup_driver_available', true)
            ->assertJsonPath('slots.0.partner_available', true)
            ->assertJsonPath('slots.0.delivery_driver_available', true);

        $response = $this->actingAs($this->customer, 'api')
            ->postJson('/api/customer/bookings', [
                'vehicle_id' => $this->vehicle->id,
                'service_id' => $this->service->id,
                'wash_type' => WashType::PICKUP_WASH,
                'booking_date' => $date,
                'booking_time' => '10:00',
                'address' => $this->address->full_address,
                'address_id' => $this->address->id,
                'latitude' => 27.1767,
                'longitude' => 78.0081,
                'payment_method' => 'cod',
            ])
            ->assertCreated()
            ->assertJsonPath('data.wash_type', WashType::PICKUP_WASH)
            ->assertJsonPath('data.status', BookingStatus::PICKUP_DRIVER_ASSIGNED);

        $this->assertDatabaseHas('bookings', [
            'id' => $response->json('data.id'),
            'pickup_driver_id' => $this->driver->id,
            'partner_id' => $this->partner->id,
            'delivery_driver_id' => $this->deliveryDriver->id,
        ]);
    }

    protected function createBooking(string $serviceMode): int
    {
        $payload = [
                'vehicle_id' => $this->vehicle->id,
                'service_id' => $this->service->id,
                'service_mode' => $serviceMode,
                'booking_date' => now()->addDay()->toDateString(),
                'booking_time' => '10:00',
                'address' => 'Home, Dayal Bagh, Agra',
                'address_id' => $this->address->id,
                'latitude' => 27.1767,
                'longitude' => 78.0081,
                'payment_method' => 'cod',
            ];

        if ($serviceMode === ServiceMode::PICKUP_DROP) {
            $payload += [
                'pickup_address_id' => $this->address->id,
                'drop_address_id' => $this->address->id,
                'pickup_date' => now()->addDay()->toDateString(),
                'pickup_time_slot' => '10:00',
            ];
        }

        $response = $this->actingAs($this->customer, 'api')
            ->postJson('/api/customer/bookings', $payload)
            ->assertCreated();

        return $response->json('data.id');
    }

    protected function uploadMedia(string $role, int $bookingId, string $type): void
    {
        $user = match ($role) {
            'worker' => $this->worker,
            'driver' => $this->driver,
            default => $this->partner,
        };

        $this->actingAs($user, 'api')
            ->postJson("/api/operations/{$role}/jobs/{$bookingId}/media", [
                'type' => $type,
                'file' => UploadedFile::fake()->create("{$type}.jpg", 64, 'image/jpeg'),
            ])
            ->assertCreated();
    }

    protected function driverStatus(int $bookingId, string $status): void
    {
        $this->actingAs($this->driver, 'api')
            ->postJson("/api/operations/driver/jobs/{$bookingId}/status", ['status' => $status])
            ->assertOk();
    }

    protected function user(string $role, string $mobile): User
    {
        return User::create([
            'name' => ucfirst(str_replace('_', ' ', $role)),
            'email' => "{$role}.{$mobile}@example.test",
            'mobile_number' => $mobile,
            'password' => 'password',
            'role' => $role,
        ]);
    }
}
