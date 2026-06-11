<?php

namespace App\Services;

use App\Constants\BookingStatus;
use App\Constants\ServiceMode;
use App\Constants\UserRole;
use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssignmentService
{
    public function __construct(
        protected BookingStateService $stateService,
        protected NotificationService $notificationService
    ) {}

    public function assignWorker(Booking $booking, int $workerId, int $adminId): Booking
    {
        if (($booking->service_mode ?? ServiceMode::PARTNER_CENTER) !== ServiceMode::DOORSTEP) {
            throw new InvalidArgumentException('Only doorstep bookings can be assigned to a worker.');
        }

        $worker = User::where('role', UserRole::WORKER)->findOrFail($workerId);

        return DB::transaction(function () use ($booking, $worker, $adminId) {
            if ($booking->status === BookingStatus::PENDING) {
                $this->stateService->transition($booking, BookingStatus::CONFIRMED, User::find($adminId), 'Booking confirmed before worker assignment');
                $booking = $booking->fresh();
            }

            $booking->forceFill(['worker_id' => $worker->id])->save();
            $this->upsertAssignment($booking, ['worker_id' => $worker->id, 'assigned_by_admin_id' => $adminId]);
            $this->stateService->transition($booking->fresh(), BookingStatus::WORKER_ASSIGNED, User::find($adminId), 'Worker assigned');
            $this->notificationService->workerAssigned($booking->fresh(), $worker);

            return $booking->fresh();
        });
    }

    public function assignPartner(Booking $booking, int $partnerId, int $adminId): Booking
    {
        $partner = User::where('role', UserRole::PARTNER)->findOrFail($partnerId);

        return DB::transaction(function () use ($booking, $partner, $adminId) {
            if ($booking->status === BookingStatus::PENDING) {
                $this->stateService->transition($booking, BookingStatus::CONFIRMED, User::find($adminId), 'Booking confirmed before partner assignment');
                $booking = $booking->fresh();
            }

            $booking->forceFill(['partner_id' => $partner->id])->save();
            $this->upsertAssignment($booking, ['partner_id' => $partner->id, 'assigned_by_admin_id' => $adminId]);

            if ($booking->service_mode === ServiceMode::PARTNER_CENTER ||
                ($booking->service_mode === ServiceMode::PICKUP_DROP && $booking->fresh()->status === BookingStatus::REACHED_PARTNER)) {
                $this->stateService->transition($booking->fresh(), BookingStatus::PARTNER_ASSIGNED, User::find($adminId), 'Partner assigned');
            }

            $this->notificationService->partnerAssigned($booking->fresh(), $partner);

            return $booking->fresh();
        });
    }

    public function assignPickupDriver(Booking $booking, int $driverId, int $adminId): Booking
    {
        if (($booking->service_mode ?? ServiceMode::PARTNER_CENTER) !== ServiceMode::PICKUP_DROP) {
            throw new InvalidArgumentException('Only pickup_drop bookings can be assigned to a pickup driver.');
        }

        $driver = User::where('role', UserRole::PICKUP_DRIVER)->findOrFail($driverId);

        return DB::transaction(function () use ($booking, $driver, $adminId) {
            if ($booking->status === BookingStatus::PENDING) {
                $this->stateService->transition($booking, BookingStatus::CONFIRMED, User::find($adminId), 'Booking confirmed before pickup driver assignment');
                $booking = $booking->fresh();
            }

            $booking->forceFill(['pickup_driver_id' => $driver->id])->save();
            $this->upsertAssignment($booking, ['pickup_driver_id' => $driver->id, 'assigned_by_admin_id' => $adminId]);
            $this->stateService->transition($booking->fresh(), BookingStatus::PICKUP_DRIVER_ASSIGNED, User::find($adminId), 'Pickup driver assigned');
            $this->notificationService->pickupDriverAssigned($booking->fresh(), $driver);

            return $booking->fresh();
        });
    }

    protected function upsertAssignment(Booking $booking, array $data): BookingAssignment
    {
        return BookingAssignment::updateOrCreate(
            ['booking_id' => $booking->id, 'status' => 'active'],
            array_merge($data, [
                'booking_id' => $booking->id,
                'assigned_by' => $data['assigned_by_admin_id'] ?? null,
                'assigned_at' => now(),
                'status' => 'active',
            ])
        );
    }
}
