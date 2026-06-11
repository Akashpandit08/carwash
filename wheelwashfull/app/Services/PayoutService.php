<?php

namespace App\Services;

use App\Constants\PayoutStatus;
use App\Constants\UserRole;
use App\Models\Booking;
use App\Models\Payout;

class PayoutService
{
    public function generateForCompletedBooking(Booking $booking): void
    {
        if ($booking->payouts()->exists()) {
            return;
        }

        $gross = (float) ($booking->total_amount ?? $booking->final_price ?? $booking->price ?? 0);

        if ($booking->worker_id) {
            $this->createPercentagePayout($booking, $booking->worker_id, UserRole::WORKER, $gross, (float) config('wheelwash.worker_commission_percentage'));
        }

        if ($booking->partner_id) {
            $this->createPercentagePayout($booking, $booking->partner_id, UserRole::PARTNER, $gross, (float) config('wheelwash.partner_commission_percentage'));
        }

        if ($booking->pickup_driver_id) {
            $amount = min((float) config('wheelwash.pickup_driver_fixed_amount'), $gross);
            $this->createPayout($booking, $booking->pickup_driver_id, UserRole::PICKUP_DRIVER, $gross, $gross - $amount, $amount);
        }
    }

    protected function createPercentagePayout(Booking $booking, int $userId, string $role, float $gross, float $percentage): void
    {
        $net = round($gross * ($percentage / 100), 2);
        $this->createPayout($booking, $userId, $role, $gross, $gross - $net, $net);
    }

    protected function createPayout(Booking $booking, int $userId, string $role, float $gross, float $commission, float $net): void
    {
        Payout::create([
            'booking_id' => $booking->id,
            'user_id' => $userId,
            'role' => $role,
            'gross_amount' => $gross,
            'commission_amount' => max($commission, 0),
            'net_amount' => max($net, 0),
            'payout_status' => PayoutStatus::PENDING,
        ]);
    }
}
