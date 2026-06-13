<?php

namespace App\Services;

use App\Constants\BookingStatus;
use App\Constants\ServiceMode;
use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookingStateService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected PayoutService $payoutService,
        protected SubscriptionUsageService $subscriptionUsageService
    ) {}

    public function transitionsFor(string $serviceMode): array
    {
        return match ($serviceMode) {
            ServiceMode::DOORSTEP => [
                BookingStatus::PENDING => [BookingStatus::CONFIRMED, BookingStatus::CANCELLED],
                BookingStatus::CONFIRMED => [BookingStatus::WORKER_ASSIGNED, BookingStatus::CANCELLED],
                BookingStatus::WORKER_ASSIGNED => [BookingStatus::WORKER_ON_THE_WAY, BookingStatus::CANCELLED],
                BookingStatus::WORKER_ON_THE_WAY => [BookingStatus::REACHED_LOCATION, BookingStatus::CANCELLED],
                BookingStatus::REACHED_LOCATION => [BookingStatus::SERVICE_STARTED, BookingStatus::CANCELLED],
                BookingStatus::SERVICE_STARTED => [BookingStatus::SERVICE_COMPLETED, BookingStatus::CANCELLED],
                BookingStatus::SERVICE_COMPLETED => [BookingStatus::CASH_COLLECTED, BookingStatus::COMPLETED],
                BookingStatus::CASH_COLLECTED => [BookingStatus::COMPLETED],
            ],
            ServiceMode::PICKUP_DROP => [
                BookingStatus::PENDING => [BookingStatus::CONFIRMED, BookingStatus::CANCELLED],
                BookingStatus::CONFIRMED => [BookingStatus::PICKUP_DRIVER_ASSIGNED, BookingStatus::CANCELLED],
                BookingStatus::PICKUP_DRIVER_ASSIGNED => [BookingStatus::DRIVER_ON_THE_WAY, BookingStatus::CANCELLED],
                BookingStatus::DRIVER_ON_THE_WAY => [BookingStatus::REACHED_LOCATION, BookingStatus::CANCELLED],
                BookingStatus::REACHED_LOCATION => [BookingStatus::CAR_PICKED_UP, BookingStatus::CANCELLED],
                BookingStatus::CAR_PICKED_UP => [BookingStatus::REACHED_PARTNER, BookingStatus::CANCELLED],
                BookingStatus::REACHED_PARTNER => [BookingStatus::PARTNER_ASSIGNED, BookingStatus::CANCELLED],
                BookingStatus::PARTNER_ASSIGNED => [BookingStatus::SERVICE_STARTED, BookingStatus::CANCELLED],
                BookingStatus::SERVICE_STARTED => [BookingStatus::SERVICE_COMPLETED, BookingStatus::CANCELLED],
                BookingStatus::SERVICE_COMPLETED => [BookingStatus::OUT_FOR_DELIVERY, BookingStatus::CANCELLED],
                BookingStatus::OUT_FOR_DELIVERY => [BookingStatus::REACHED_DELIVERY_LOCATION, BookingStatus::CANCELLED],
                BookingStatus::REACHED_DELIVERY_LOCATION => [BookingStatus::DELIVERED, BookingStatus::CANCELLED],
                BookingStatus::DELIVERED => [BookingStatus::CASH_COLLECTED, BookingStatus::COMPLETED],
                BookingStatus::CASH_COLLECTED => [BookingStatus::COMPLETED],
            ],
            default => [
                BookingStatus::PENDING => [BookingStatus::CONFIRMED, BookingStatus::CANCELLED],
                BookingStatus::CONFIRMED => [BookingStatus::PARTNER_ASSIGNED, BookingStatus::CANCELLED],
                BookingStatus::PARTNER_ASSIGNED => [BookingStatus::SERVICE_STARTED, BookingStatus::CANCELLED],
                BookingStatus::SERVICE_STARTED => [BookingStatus::SERVICE_COMPLETED, BookingStatus::CANCELLED],
                BookingStatus::SERVICE_COMPLETED => [BookingStatus::COMPLETED],
            ],
        };
    }

    public function canTransition(Booking $booking, string $newStatus, bool $adminOverride = false): bool
    {
        if ($adminOverride && $newStatus === BookingStatus::CANCELLED) {
            return true;
        }

        $serviceMode = $booking->service_mode ?? match ($booking->wash_type) {
            'door_to_door' => ServiceMode::DOORSTEP,
            'pickup_wash', 'pickup_drop' => ServiceMode::PICKUP_DROP,
            default => ServiceMode::PARTNER_CENTER,
        };

        $allowed = $this->transitionsFor($serviceMode)[$booking->status] ?? [];

        return in_array($newStatus, $allowed, true);
    }

    public function transition(Booking $booking, string $newStatus, ?User $actor = null, ?string $note = null, bool $adminOverride = false): Booking
    {
        if (!$this->canTransition($booking, $newStatus, $adminOverride)) {
            throw new InvalidArgumentException("Cannot transition booking {$booking->id} from {$booking->status} to {$newStatus}.");
        }

        return DB::transaction(function () use ($booking, $newStatus, $actor, $note) {
            $oldStatus = $booking->status;
            $booking->forceFill(['status' => $newStatus])->save();

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by_user_id' => $actor?->id,
                'note' => $note,
            ]);

            $this->notificationService->statusChanged($booking, $oldStatus, $newStatus, $actor);

            if ($newStatus === BookingStatus::COMPLETED) {
                $this->subscriptionUsageService->markBookingUsed($booking->fresh());
                $this->payoutService->generateForCompletedBooking($booking->fresh());
            } elseif ($newStatus === BookingStatus::CANCELLED) {
                $this->subscriptionUsageService->markBookingCancelled($booking->fresh());
            }

            return $booking->fresh();
        });
    }
}
