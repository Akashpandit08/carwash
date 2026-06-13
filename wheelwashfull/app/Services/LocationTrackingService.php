<?php

namespace App\Services;

use App\Constants\UserRole;
use App\Models\Booking;
use App\Models\LiveLocation;
use App\Models\User;
use App\Constants\ServiceMode;

class LocationTrackingService
{
    public function update(User $user, array $data): LiveLocation
    {
        if (isset($data['booking_id'])) {
            $booking = Booking::find($data['booking_id']);
            if ($booking && !in_array($user->id, [$booking->worker_id, $booking->pickup_driver_id, $booking->delivery_driver_id])) {
                abort(403, 'Unauthorized to update location for this booking.');
            }
        }
        return LiveLocation::create([
            'user_id' => $user->id,
            'booking_id' => $data['booking_id'] ?? null,
            'role' => $user->role,
            'is_online' => $data['is_online'] ?? true,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'heading' => $data['heading'] ?? null,
            'speed' => $data['speed'] ?? null,
            'recorded_at' => $data['recorded_at'] ?? now(),
        ]);
    }

    public function latestForBooking(Booking $booking)
    {
        $ids = array_filter([$booking->worker_id, $booking->pickup_driver_id, $booking->delivery_driver_id]);

        return LiveLocation::with('user')
            ->where('booking_id', $booking->id)
            ->whereIn('user_id', $ids)
            ->whereIn('role', [UserRole::WORKER, UserRole::PICKUP_DRIVER])
            ->latest('recorded_at')
            ->get()
            ->unique('user_id')
            ->values();
    }

    public function trackingSnapshot(Booking $booking): array
    {
        $booking->loadMissing(['pickupAddress', 'dropAddress', 'partner.partnerProfile']);
        $workerLocation = $booking->worker_id ? $this->latestForUser($booking, $booking->worker_id, UserRole::WORKER) : null;
        $driverId = $booking->pickup_driver_id ?: $booking->delivery_driver_id;
        $driverLocation = $driverId ? $this->latestForUser($booking, $driverId, UserRole::PICKUP_DRIVER) : null;

        return [
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'service_mode' => $booking->service_mode,
            'wash_type' => $booking->wash_type,
            'worker_location' => $workerLocation,
            'driver_location' => $driverLocation,
            'customer_location' => $this->customerLocation($booking),
            'partner_location' => $this->partnerLocation($booking),
        ];
    }

    protected function latestForUser(Booking $booking, int $userId, string $role): ?array
    {
        $location = LiveLocation::where('booking_id', $booking->id)
            ->where('user_id', $userId)
            ->where('role', $role)
            ->latest('recorded_at')
            ->first();

        if (!$location) {
            $location = LiveLocation::where('user_id', $userId)
                ->where('role', $role)
                ->latest('recorded_at')
                ->first();
        }

        if (!$location) {
            return null;
        }

        return [
            'latitude' => (float) $location->latitude,
            'longitude' => (float) $location->longitude,
            'heading' => $location->heading !== null ? (float) $location->heading : null,
            'speed' => $location->speed !== null ? (float) $location->speed : null,
            'is_online' => (bool) $location->is_online,
            'last_seen_at' => $location->recorded_at?->toIso8601String(),
        ];
    }

    protected function customerLocation(Booking $booking): ?array
    {
        $address = in_array($booking->service_mode, [ServiceMode::PICKUP_DROP], true)
            ? ($booking->pickupAddress ?: $booking->dropAddress)
            : null;

        $latitude = $address?->latitude ?? $booking->latitude;
        $longitude = $address?->longitude ?? $booking->longitude;

        if ($latitude === null || $longitude === null) {
            return null;
        }

        return [
            'latitude' => (float) $latitude,
            'longitude' => (float) $longitude,
        ];
    }

    protected function partnerLocation(Booking $booking): ?array
    {
        $profile = $booking->partner?->partnerProfile;

        if (!$profile || $profile->latitude === null || $profile->longitude === null) {
            return null;
        }

        return [
            'latitude' => (float) $profile->latitude,
            'longitude' => (float) $profile->longitude,
        ];
    }
}
