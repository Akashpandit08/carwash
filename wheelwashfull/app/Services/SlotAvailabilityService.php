<?php

namespace App\Services;

use App\Constants\UserRole;
use App\Constants\WashType;
use App\Models\Booking;
use App\Models\Slot;
use Carbon\Carbon;
use InvalidArgumentException;

class SlotAvailabilityService
{
    public const RADIUS_KM = 10;

    public function __construct(protected DistanceService $distanceService) {}

    public function getAvailableSlots(string $washType, float $lat, float $lng, string $date, ?int $serviceId = null): array
    {
        return collect($this->baseSlots($date))
            ->map(fn (array $slot) => $this->availabilityForSlot($washType, $lat, $lng, $date, $slot))
            ->values()
            ->all();
    }

    protected function availabilityForSlot(string $washType, float $lat, float $lng, string $date, array $slot): array
    {
        return match ($washType) {
            WashType::DOOR_TO_DOOR => $this->doorToDoorAvailability($lat, $lng, $date, $slot),
            WashType::PICKUP_WASH => $this->pickupWashAvailability($lat, $lng, $date, $slot),
            default => throw new InvalidArgumentException("Unsupported wash type [$washType]."),
        };
    }

    protected function doorToDoorAvailability(float $lat, float $lng, string $date, array $slot): array
    {
        $worker = $this->distanceService->getNearestAvailable(
            UserRole::WORKER,
            $lat,
            $lng,
            self::RADIUS_KM,
            $date,
            $slot['time']
        );

        return array_merge($slot, [
            'available' => $slot['available_count'] > 0 && $worker !== null,
            'worker_available' => $worker !== null,
            'nearest_distance_km' => $worker?->distance_km,
        ]);
    }

    protected function pickupWashAvailability(float $lat, float $lng, string $date, array $slot): array
    {
        $pickupDriver = $this->distanceService->getNearestAvailable(
            UserRole::PICKUP_DRIVER,
            $lat,
            $lng,
            self::RADIUS_KM,
            $date,
            $slot['time']
        );
        $partner = $this->distanceService->getNearestAvailable(
            UserRole::PARTNER,
            $lat,
            $lng,
            self::RADIUS_KM,
            $date,
            $slot['time']
        );
        $deliveryDriver = $pickupDriver
            ? $this->distanceService->getNearestAvailable(
                UserRole::PICKUP_DRIVER,
                $lat,
                $lng,
                self::RADIUS_KM,
                $date,
                $slot['time'],
                [$pickupDriver->id]
            )
            : null;

        return array_merge($slot, [
            'available' => $slot['available_count'] > 0 && $pickupDriver !== null && $partner !== null && $deliveryDriver !== null,
            'pickup_driver_available' => $pickupDriver !== null,
            'partner_available' => $partner !== null,
            'delivery_driver_available' => $deliveryDriver !== null,
            'nearest_pickup_driver_distance_km' => $pickupDriver?->distance_km,
            'nearest_partner_distance_km' => $partner?->distance_km,
            'nearest_delivery_driver_distance_km' => $deliveryDriver?->distance_km,
        ]);
    }

    protected function baseSlots(string $date): array
    {
        $existingBookings = Booking::whereDate('booking_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->get()
            ->groupBy(fn (Booking $booking) => Carbon::parse($booking->slot_time)->format('H:i'));

        return Slot::whereDate('date', $date)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get()
            ->map(function (Slot $slot) use ($date, $existingBookings) {
                $slotTime = Carbon::parse($slot->start_time)->format('H:i');
                $bookingsCount = isset($existingBookings[$slotTime]) ? $existingBookings[$slotTime]->count() : 0;
                $availableCount = max(0, $slot->max_bookings - $bookingsCount);
                $startsAt = Carbon::parse($date . ' ' . $slotTime);

                return [
                    'id' => $slot->id,
                    'time' => $slotTime,
                    'available_count' => $availableCount,
                    'max_bookings' => $slot->max_bookings,
                    'is_past' => $startsAt->isPast(),
                ];
            })
            ->reject(fn (array $slot) => $slot['is_past'])
            ->map(function (array $slot) {
                unset($slot['is_past']);

                return $slot;
            })
            ->unique('time')
            ->values()
            ->all();
    }
}
