<?php

namespace Tests\Feature;

use App\Constants\BookingStatus;
use App\Models\Booking;
use App\Models\CustomerSubscription;
use App\Models\Service;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\BookingStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_booking_with_subscription_and_it_deducts_on_completion()
    {
        // 1. Setup Data
        $customer = User::factory()->create(['role' => 'customer', 'mobile_number' => '1234567890']);
        $worker = User::factory()->create(['role' => 'worker', 'mobile_number' => '0987654321']);
        
        $plan = SubscriptionPlan::factory()->create([
            'total_washes' => 4,
            'exterior_washes' => 4,
        ]);

        $subscription = CustomerSubscription::factory()->create([
            'user_id' => $customer->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'remaining_washes' => 4,
            'exterior_remaining' => 4,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(30),
        ]);

        $service = Service::factory()->create(['price' => 500, 'is_active' => true]);
        $vehicle = Vehicle::factory()->create(['user_id' => $customer->id]);

        $this->mock(\App\Services\SlotAvailabilityService::class, function ($mock) {
            $mock->shouldReceive('getAvailableSlots')->andReturn([
                ['time' => '10:00:00', 'available' => true]
            ]);
        });

        $this->mock(\App\Services\BookingAssignmentService::class, function ($mock) use ($worker) {
            $mock->shouldReceive('assignForDoorToDoor')->andReturnUsing(function ($booking) use ($worker) {
                $booking->worker_id = $worker->id;
                $booking->save();
                return $booking;
            });
        });
        
        $response = $this->actingAs($customer, 'api')->postJson('/api/customer/bookings', [
            'service_id' => $service->id,
            'vehicle_id' => $vehicle->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '10:00:00',
            'address' => 'Test Address',
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'payment_method' => 'subscription',
            'customer_subscription_id' => $subscription->id,
            'wash_type' => 'door_to_door',
        ]);

        if ($response->status() !== 201) {
            dump($response->json());
        }
        $response->assertStatus(201);
        $this->assertEquals(0, $response->json('data.payable_amount'));
        $this->assertEquals('subscription', $response->json('data.booking_source'));
        $this->assertEquals('subscription', $response->json('data.payment_method'));
        $this->assertEquals('paid', $response->json('data.payment_status'));

        $booking = Booking::find($response->json('data.id'));
        $booking->update(['worker_id' => $worker->id]);

        // Check deduction hasn't happened yet
        $this->assertEquals(4, $subscription->fresh()->remaining_washes);

        // 3. Worker completes service
        $stateService = app(BookingStateService::class);
        
        // Progress to service completed
        $stateService->transition($booking, BookingStatus::CONFIRMED);
        $stateService->transition($booking, BookingStatus::WORKER_ASSIGNED);
        $stateService->transition($booking, BookingStatus::WORKER_ON_THE_WAY);
        $stateService->transition($booking, BookingStatus::REACHED_LOCATION);
        $stateService->transition($booking, BookingStatus::SERVICE_STARTED);
        $stateService->transition($booking, BookingStatus::SERVICE_COMPLETED);

        // At this point, washes still should not be deducted
        $this->assertEquals(4, $subscription->fresh()->remaining_washes);

        // Worker marks it complete via the "Mark as Completed" API equivalent
        // This triggers COMPLETED status
        $stateService->transition($booking, BookingStatus::COMPLETED);

        // 4. Verify deduction
        $subscription->refresh();
        $this->assertEquals(3, $subscription->remaining_washes);
        $this->assertEquals(1, $subscription->used_washes);
        
        // 5. Verify Idempotency (prevent duplicate deduction)
        app(\App\Services\SubscriptionUsageService::class)->markBookingUsed($booking);
        $subscription->refresh();
        $this->assertEquals(3, $subscription->remaining_washes); // Should still be 3
    }
}
