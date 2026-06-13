<?php

namespace App\Services;

use App\Constants\BookingStatus;
use App\Constants\UserRole;
use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookingAssignmentService
{
    public function __construct(
        protected DistanceService $distanceService,
        protected BookingStateService $stateService,
        protected NotificationService $notificationService
    ) {}

    public function assignForDoorToDoor(Booking $booking, float $lat, float $lng): Booking
    {
        $worker = $this->distanceService->getNearestAvailable(
            UserRole::WORKER,
            $lat,
            $lng,
            SlotAvailabilityService::RADIUS_KM,
            $booking->booking_date->toDateString(),
            $this->slotTime($booking)
        );

        if (!$worker) {
            throw new InvalidArgumentException('No available slot within 10 KM for selected location.');
        }

        return DB::transaction(function () use ($booking, $worker) {
            $booking = $this->confirmIfPending($booking, 'Booking auto-confirmed before worker assignment');
            $booking->forceFill([
                'worker_id' => $worker->id,
                'service_city_id' => $worker->service_city_id,
                'service_zone_id' => $worker->service_zone_id,
            ])->save();
            $this->upsertAssignment($booking->fresh(), ['worker_id' => $worker->id], 'Auto-assigned nearest worker');
            $booking = $this->stateService->transition($booking->fresh(), BookingStatus::WORKER_ASSIGNED, null, 'Worker auto-assigned');
            $this->notificationService->workerAssigned($booking->fresh(), $worker);

            return $booking->fresh();
        });
    }

    public function assignForPickupWash(Booking $booking, float $lat, float $lng): Booking
    {
        $slotTime = $this->slotTime($booking);
        $date = $booking->booking_date->toDateString();

        $driver = $this->distanceService->getNearestAvailable(UserRole::PICKUP_DRIVER, $lat, $lng, SlotAvailabilityService::RADIUS_KM, $date, $slotTime);
        $partner = $this->distanceService->getNearestAvailable(UserRole::PARTNER, $lat, $lng, SlotAvailabilityService::RADIUS_KM, $date, $slotTime);
        $deliveryDriver = $driver
            ? $this->distanceService->getNearestAvailable(UserRole::PICKUP_DRIVER, $lat, $lng, SlotAvailabilityService::RADIUS_KM, $date, $slotTime, [$driver->id])
            : null;

        if (!$driver || !$partner || !$deliveryDriver) {
            throw new InvalidArgumentException('No available slot within 10 KM for selected location.');
        }

        return DB::transaction(function () use ($booking, $driver, $partner, $deliveryDriver) {
            $booking = $this->confirmIfPending($booking, 'Booking auto-confirmed before pickup assignment');
            $booking->forceFill([
                'pickup_driver_id' => $driver->id,
                'delivery_driver_id' => $deliveryDriver->id,
                'partner_id' => $partner->id,
                'service_city_id' => $partner->service_city_id,
                'service_zone_id' => $partner->service_zone_id,
            ])->save();
            $this->upsertAssignment($booking->fresh(), [
                'pickup_driver_id' => $driver->id,
                'delivery_driver_id' => $deliveryDriver->id,
                'partner_id' => $partner->id,
            ], 'Auto-assigned nearest pickup driver, partner, and delivery driver');
            $booking = $this->stateService->transition($booking->fresh(), BookingStatus::PICKUP_DRIVER_ASSIGNED, null, 'Pickup driver auto-assigned');
            $this->notificationService->pickupDriverAssigned($booking->fresh(), $driver);
            $this->notificationService->partnerAssigned($booking->fresh(), $partner);

            return $booking->fresh();
        });
    }

    protected function confirmIfPending(Booking $booking, string $note): Booking
    {
        if ($booking->status !== BookingStatus::PENDING) {
            return $booking;
        }

        return $this->stateService->transition($booking, BookingStatus::CONFIRMED, null, $note);
    }

    protected function upsertAssignment(Booking $booking, array $data, string $notes): BookingAssignment
    {
        return BookingAssignment::updateOrCreate(
            ['booking_id' => $booking->id, 'status' => 'active'],
            array_merge($data, [
                'booking_id' => $booking->id,
                'assigned_by' => null,
                'assigned_by_admin_id' => null,
                'assigned_at' => now(),
                'status' => 'active',
                'notes' => $notes,
            ])
        );
    }

    protected function slotTime(Booking $booking): string
    {
        return \Carbon\Carbon::parse($booking->slot_time)->format('H:i');
    }
}
