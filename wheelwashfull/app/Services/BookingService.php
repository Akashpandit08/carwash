<?php

namespace App\Services;

use App\Constants\ServiceMode;
use App\Constants\WashType;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    const MAX_BOOKINGS_PER_SLOT = 3;

    public function __construct(
        protected SlotService $slotService,
        protected CouponService $couponService,
        protected PaymentService $paymentService,
        protected BookingAssignmentService $bookingAssignmentService
    ) {}

    /**
     * Generate available time slots for a given date and service.
     */
    public function generateTimeSlots(string $date, int $serviceId): array
    {
        $availableSlots = $this->slotService->getAvailableSlots($date, $serviceId);
        // We return just the string times to match existing API usage
        return array_column($availableSlots, 'time');
    }

    /**
     * Calculate pricing with coupon discount.
     */
    public function calculatePricing(int $serviceId, ?string $couponCode = null): array
    {
        $service = Service::findOrFail($serviceId);
        $price = $service->price;

        if ($couponCode) {
            $result = $this->couponService->validateAndCalculate(strtoupper($couponCode), $price);
            
            return [
                'success' => $result['valid'],
                'message' => $result['message'],
                'original_price' => $price,
                'discount' => $result['discount'],
                'final_price' => $result['final_amount'],
                'pickup_fee' => 0,
                'drop_fee' => 0,
                'coupon_id' => $result['coupon_id'] ?? null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Pricing calculated',
            'original_price' => $price,
            'discount' => 0,
            'final_price' => $price,
            'pickup_fee' => 0,
            'drop_fee' => 0,
            'coupon_id' => null,
        ];
    }

    /**
     * Calculate pickup and drop fees based on service mode.
     */
    public function calculateDeliveryFees(string $serviceMode): array
    {
        if ($serviceMode === ServiceMode::PICKUP_DROP) {
            return [
                'pickup_fee' => 50.00,
                'drop_fee' => 50.00,
            ];
        }

        return [
            'pickup_fee' => 0.00,
            'drop_fee' => 0.00,
        ];
    }

    /**
     * Generate unique booking number.
     */
    public function generateBookingNumber(): string
    {
        $datePrefix = 'WM-' . now()->format('Ymd') . '-';
        
        $latestBooking = Booking::where('booking_number', 'like', $datePrefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestBooking) {
            return $datePrefix . '0001';
        }

        $sequence = (int) Str::afterLast($latestBooking->booking_number, '-');
        $nextSequence = str_pad($sequence + 1, 4, '0', STR_PAD_LEFT);

        return $datePrefix . $nextSequence;
    }

    /**
     * Create a booking and initialize payment.
     */
    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $pricing = $this->calculatePricing($data['service_id'], $data['coupon_code'] ?? null);
            $service = Service::findOrFail($data['service_id']);

            // If coupon was applied successfully, increment its usage count
            if ($pricing['coupon_id']) {
                $this->couponService->incrementUsage($pricing['coupon_id']);
            }

            $washType = $data['wash_type'] ?? null;
            $serviceMode = $this->serviceModeForWashType($washType, $data['service_mode'] ?? ServiceMode::PARTNER_CENTER);
            $fees = $this->calculateDeliveryFees($serviceMode);
            $totalAmount = $pricing['final_price'] + $fees['pickup_fee'] + $fees['drop_fee'];

            $payableAmount = $totalAmount;
            $subscription = null;

            if (!empty($data['customer_subscription_id'])) {
                $subscription = \App\Models\CustomerSubscription::where('id', $data['customer_subscription_id'])
                    ->where('user_id', $data['user_id'])
                    ->where('status', 'active')
                    ->where('remaining_washes', '>', 0)
                    ->whereDate('start_date', '<=', $data['booking_date'])
                    ->whereDate('end_date', '>=', $data['booking_date'])
                    ->first();

                if (!$subscription) {
                    throw new \InvalidArgumentException('Invalid, exhausted, or expired subscription.');
                }

                $payableAmount = 0;
                $data['booking_source'] = 'subscription';
                $data['payment_method'] = 'subscription';
                $data['subscription_wash_type'] = $washType ?? 'exterior';
            }

            $booking = Booking::create([
                'booking_number' => $this->generateBookingNumber(),
                'user_id' => $data['user_id'],
                'customer_subscription_id' => $data['customer_subscription_id'] ?? null,
                'booking_source' => $data['booking_source'] ?? 'normal',
                'subscription_wash_type' => $data['subscription_wash_type'] ?? null,
                'vehicle_id' => $data['vehicle_id'],
                'service_id' => $data['service_id'],
                'service_city_id' => $service->service_city_id ?? $data['service_city_id'] ?? null,
                'service_zone_id' => $service->service_zone_id ?? $data['service_zone_id'] ?? null,
                'service_mode' => $serviceMode,
                'wash_type' => $washType,
                'booking_date' => $data['booking_date'],
                'slot_time' => $data['slot_time'],
                'address' => $data['address'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'price' => $pricing['original_price'],
                'discount' => $pricing['discount'],
                'final_price' => $pricing['final_price'],
                'total_amount' => $totalAmount,
                'payable_amount' => $payableAmount,
                'coupon_id' => $pricing['coupon_id'],
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'status' => 'pending',
                'pickup_address_id' => $data['pickup_address_id'] ?? $data['address_id'] ?? null,
                'drop_address_id' => $data['drop_address_id'] ?? $data['address_id'] ?? null,
                'pickup_fee' => $fees['pickup_fee'],
                'drop_fee' => $fees['drop_fee'],
                'pickup_scheduled_at' => isset($data['pickup_date'], $data['pickup_time_slot'])
                    ? \Carbon\Carbon::parse($data['pickup_date'] . ' ' . $data['pickup_time_slot'])
                    : null,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($subscription) {
                \App\Models\SubscriptionBooking::create([
                    'customer_subscription_id' => $subscription->id,
                    'booking_id' => $booking->id,
                    'wash_type' => $data['subscription_wash_type'] ?? 'exterior',
                    'status' => 'reserved',
                ]);
            }

            if ($washType === WashType::DOOR_TO_DOOR) {
                $booking = $this->bookingAssignmentService->assignForDoorToDoor($booking, (float) $data['latitude'], (float) $data['longitude']);
            } elseif ($washType === WashType::PICKUP_WASH) {
                $booking = $this->bookingAssignmentService->assignForPickupWash($booking, (float) $data['latitude'], (float) $data['longitude']);
            }

            $this->paymentService->initialize($booking, $data['payment_method']);

            return $booking->fresh(['latestPayment']);
        });
    }

    protected function serviceModeForWashType(?string $washType, string $fallback): string
    {
        return match ($washType) {
            WashType::DOOR_TO_DOOR => ServiceMode::DOORSTEP,
            WashType::PICKUP_WASH => ServiceMode::PICKUP_DROP,
            default => $fallback,
        };
    }

    /**
     * Cancel a booking and refund coupon usage.
     */
    public function cancelBooking(Booking $booking, string $reason = null): Booking
    {
        // If booking used a coupon, decrement its usage count
        if ($booking->coupon_id) {
            $this->couponService->decrementUsage($booking->coupon_id);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
        ]);

        return $booking;
    }
}
