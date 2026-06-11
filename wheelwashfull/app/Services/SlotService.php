<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Slot;
use Carbon\Carbon;

class SlotService
{
    /**
     * Get available slots for a given date.
     */
    public function getAvailableSlots(string $date, ?int $serviceId = null): array
    {
        $slots = Slot::whereDate('date', $date)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        $existingBookings = Booking::where('booking_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->get()
            ->groupBy('slot_time');

        $availableSlots = [];

        foreach ($slots as $slot) {
            $slotTime = Carbon::parse($slot->start_time)->format('H:i');
            
            // For now we check count by slot_time exactly matching start_time
            $bookingsCount = isset($existingBookings[$slotTime]) ? $existingBookings[$slotTime]->count() : 0;
            $availableCount = max(0, $slot->max_bookings - $bookingsCount);

            if ($availableCount > 0) {
                // If the date is today, only show future slots
                if (Carbon::parse($date)->isToday() && Carbon::parse($slot->start_time)->isPast()) {
                    continue;
                }
                
                $availableSlots[] = [
                    'id' => $slot->id,
                    'time' => $slotTime,
                    'available_count' => $availableCount,
                    'max_bookings' => $slot->max_bookings,
                ];
            }
        }

        return $availableSlots;
    }
}
