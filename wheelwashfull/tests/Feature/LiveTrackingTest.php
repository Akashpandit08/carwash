<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class LiveTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_worker_can_update_location()
    {
        $worker = User::factory()->create(['role' => 'worker']);
        $service = \App\Models\Service::factory()->create();
        $vehicle = \App\Models\Vehicle::factory()->create(['user_id' => $worker->id]); // just for dummy
        $booking = Booking::create([
            'user_id' => $worker->id, // dummy user
            'service_id' => $service->id,
            'vehicle_id' => $vehicle->id,
            'worker_id' => $worker->id,
            'booking_date' => now()->toDateString(),
            'slot_time' => '10:00:00',
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'address' => 'Test Address',
            'price' => 100,
            'final_price' => 100,
            'status' => 'assigned',
            'payment_status' => 'pending',
            'total_amount' => 100,
            'payable_amount' => 100
        ]);

        $response = $this->actingAs($worker, 'api')->postJson('/api/operations/location/update', [
            'booking_id' => $booking->id,
            'latitude' => 12.9716,
            'longitude' => 77.5946,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('live_locations', [
            'booking_id' => $booking->id,
            'user_id' => $worker->id,
            'latitude' => 12.9716,
            'longitude' => 77.5946,
        ]);
    }

    public function test_unrelated_worker_cannot_update_location()
    {
        $worker = User::factory()->create(['role' => 'worker']);
        $unrelatedWorker = User::factory()->create(['role' => 'worker']);
        $service = \App\Models\Service::factory()->create();
        $vehicle = \App\Models\Vehicle::factory()->create(['user_id' => $worker->id]); // just for dummy
        $booking = Booking::create([
            'user_id' => $worker->id, // dummy user
            'service_id' => $service->id,
            'vehicle_id' => $vehicle->id,
            'worker_id' => $worker->id,
            'booking_date' => now()->toDateString(),
            'slot_time' => '10:00:00',
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'address' => 'Test Address',
            'price' => 100,
            'final_price' => 100,
            'status' => 'assigned',
            'payment_status' => 'pending',
            'total_amount' => 100,
            'payable_amount' => 100
        ]);

        $response = $this->actingAs($unrelatedWorker, 'api')->postJson('/api/operations/location/update', [
            'booking_id' => $booking->id,
            'latitude' => 12.9716,
            'longitude' => 77.5946,
        ]);

        $response->assertStatus(403);
    }

    public function test_booking_customer_can_view_tracking()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $worker = User::factory()->create(['role' => 'worker']);
        $service = \App\Models\Service::factory()->create();
        $vehicle = \App\Models\Vehicle::factory()->create(['user_id' => $customer->id]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'vehicle_id' => $vehicle->id,
            'worker_id' => $worker->id,
            'booking_date' => now()->toDateString(),
            'slot_time' => '10:00:00',
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'address' => 'Test Address',
            'price' => 100,
            'final_price' => 100,
            'status' => 'assigned',
            'payment_status' => 'pending',
            'total_amount' => 100,
            'payable_amount' => 100
        ]);

        \App\Models\LiveLocation::create([
            'booking_id' => $booking->id,
            'user_id' => $worker->id,
            'role' => 'worker',
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'heading' => 90,
            'speed' => 10,
            'recorded_at' => now(),
        ]);

        $response1 = $this->actingAs($customer, 'api')->getJson("/api/customer/bookings/{$booking->id}/track");
        $response1->assertStatus(200);
        
        $response2 = $this->getJson("/api/customer/bookings/{$booking->id}/tracking");
        $response2->assertStatus(200);

        $this->assertEquals(12.9716, $response1->json('data.worker_location.latitude'));
    }

    public function test_another_customer_cannot_view_tracking()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $anotherCustomer = User::factory()->create(['role' => 'customer']);
        $worker = User::factory()->create(['role' => 'worker']);
        $service = \App\Models\Service::factory()->create();
        $vehicle = \App\Models\Vehicle::factory()->create(['user_id' => $customer->id]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'vehicle_id' => $vehicle->id,
            'worker_id' => $worker->id,
            'booking_date' => now()->toDateString(),
            'slot_time' => '10:00:00',
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'address' => 'Test Address',
            'price' => 100,
            'final_price' => 100,
            'status' => 'assigned',
            'payment_status' => 'pending',
            'total_amount' => 100,
            'payable_amount' => 100
        ]);

        $response = $this->actingAs($anotherCustomer, 'api')->getJson("/api/customer/bookings/{$booking->id}/track");
        $response->assertStatus(403); // or 404 depending on how scoped the route is
    }
}
