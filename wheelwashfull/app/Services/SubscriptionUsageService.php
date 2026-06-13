<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\SubscriptionBooking;
use Illuminate\Support\Facades\DB;

class SubscriptionUsageService
{
    public function markBookingUsed(Booking $booking): void
    {
        if (($booking->booking_source ?? 'normal') !== 'subscription') {
            return;
        }

        DB::transaction(function () use ($booking) {
            $subscriptionBooking = SubscriptionBooking::where('booking_id', $booking->id)
                ->where('status', 'reserved')
                ->lockForUpdate()
                ->first();

            if (! $subscriptionBooking) {
                return;
            }

            $subscription = $subscriptionBooking->customerSubscription()->lockForUpdate()->first();
            if (! $subscription || $subscription->remaining_washes <= 0) {
                return;
            }

            $washColumn = match ($subscriptionBooking->wash_type) {
                'exterior' => 'exterior_remaining',
                'interior' => 'interior_remaining',
                'foam' => 'foam_remaining',
                default => null,
            };

            if ($washColumn && $subscription->{$washColumn} <= 0) {
                return;
            }

            $subscriptionBooking->update([
                'status' => 'used',
                'used_at' => now(),
            ]);

            $updates = [
                'used_washes' => $subscription->used_washes + 1,
                'remaining_washes' => max(0, $subscription->remaining_washes - 1),
            ];

            if ($washColumn) {
                $updates[$washColumn] = max(0, $subscription->{$washColumn} - 1);
            }

            $subscription->update($updates);
        });
    }

    public function markBookingCancelled(Booking $booking): void
    {
        if (($booking->booking_source ?? 'normal') !== 'subscription') {
            return;
        }

        SubscriptionBooking::where('booking_id', $booking->id)
            ->where('status', 'reserved')
            ->update(['status' => 'cancelled']);
    }
}
