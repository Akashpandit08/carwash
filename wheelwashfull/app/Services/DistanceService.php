<?php

namespace App\Services;

use App\Constants\UserRole;
use App\Models\Booking;
use App\Models\LiveLocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class DistanceService
{
    public function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function nearbyUsers(string $role, float $lat, float $lng, float $radiusKm = 50): Collection
    {
        return $this->candidateUsers($role)
            ->map(function (User $user) use ($role, $lat, $lng) {
                $coords = $this->coordinatesFor($user, $role);

                if (!$coords) {
                    return null;
                }

                $distance = $this->haversineDistance($lat, $lng, $coords['latitude'], $coords['longitude']);
                $user->setAttribute('distance_km', round($distance, 2));
                $user->setAttribute('location_source', $coords['source']);

                return $user;
            })
            ->filter()
            ->filter(fn (User $user) => $user->distance_km <= $radiusKm)
            ->sortBy('distance_km')
            ->values();
    }

    public function getNearestAvailable(
        string $role,
        float $lat,
        float $lng,
        float $radiusKm,
        string $date,
        string $slotTime,
        array $excludeUserIds = []
    ): ?User {
        return $this->nearbyUsers($role, $lat, $lng, $radiusKm)
            ->reject(fn (User $user) => in_array((int) $user->id, array_map('intval', $excludeUserIds), true))
            ->first(fn (User $user) => !$this->isBooked($user, $role, $date, $slotTime));
    }

    public function isBooked(User $user, string $role, string $date, string $slotTime): bool
    {
        $columns = match ($role) {
            UserRole::WORKER => ['worker_id'],
            UserRole::PICKUP_DRIVER => ['pickup_driver_id', 'delivery_driver_id'],
            UserRole::PARTNER => ['partner_id'],
            default => throw new InvalidArgumentException("Unsupported role [$role]."),
        };

        $query = Booking::whereDate('booking_date', $date)
            ->where('slot_time', $slotTime)
            ->whereNotIn('status', ['cancelled']);

        $query->where(function ($bookingQuery) use ($columns, $user) {
            foreach ($columns as $column) {
                $bookingQuery->orWhere($column, $user->id);
            }
        });

        return $query->exists();
    }

    protected function candidateUsers(string $role): Collection
    {
        return match ($role) {
            UserRole::WORKER => User::where('role', UserRole::WORKER)
                ->whereHas('workerProfile', fn ($query) => $query->whereIn('current_status', ['available', 'active']))
                ->with('workerProfile')
                ->get(),
            UserRole::PICKUP_DRIVER => User::where('role', UserRole::PICKUP_DRIVER)
                ->whereHas('pickupDriverProfile', fn ($query) => $query->whereIn('current_status', ['available', 'active']))
                ->with('pickupDriverProfile')
                ->get(),
            UserRole::PARTNER => User::where('role', UserRole::PARTNER)
                ->whereHas('partnerProfile', fn ($query) => $query->where('current_status', 'active'))
                ->with('partnerProfile')
                ->get(),
            default => throw new InvalidArgumentException("Unsupported role [$role]."),
        };
    }

    protected function coordinatesFor(User $user, string $role): ?array
    {
        $recentLiveLocation = LiveLocation::where('user_id', $user->id)
            ->where('role', $role)
            ->where('recorded_at', '>=', Carbon::now()->subMinutes(30))
            ->latest('recorded_at')
            ->first();

        if ($recentLiveLocation) {
            return [
                'latitude' => (float) $recentLiveLocation->latitude,
                'longitude' => (float) $recentLiveLocation->longitude,
                'source' => 'live_location',
            ];
        }

        $profile = match ($role) {
            UserRole::WORKER => $user->workerProfile,
            UserRole::PICKUP_DRIVER => $user->pickupDriverProfile,
            UserRole::PARTNER => $user->partnerProfile,
            default => null,
        };

        if (!$profile || $profile->latitude === null || $profile->longitude === null) {
            return null;
        }

        return [
            'latitude' => (float) $profile->latitude,
            'longitude' => (float) $profile->longitude,
            'source' => 'profile',
        ];
    }
}
