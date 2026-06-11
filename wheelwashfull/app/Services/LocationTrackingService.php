<?php

namespace App\Services;

use App\Constants\UserRole;
use App\Models\Booking;
use App\Models\LiveLocation;
use App\Models\User;

class LocationTrackingService
{
    public function update(User $user, array $data): LiveLocation
    {
        return LiveLocation::create([
            'user_id' => $user->id,
            'booking_id' => $data['booking_id'] ?? null,
            'role' => $user->role,
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
}
