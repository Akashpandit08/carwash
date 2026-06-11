<?php

namespace Database\Seeders;

use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the time slots for a typical day
        $dailySlots = [
            ['start_time' => '09:00:00', 'end_time' => '10:00:00'],
            ['start_time' => '10:00:00', 'end_time' => '11:00:00'],
            ['start_time' => '11:00:00', 'end_time' => '12:00:00'],
            ['start_time' => '13:00:00', 'end_time' => '14:00:00'],
            ['start_time' => '14:00:00', 'end_time' => '15:00:00'],
            ['start_time' => '15:00:00', 'end_time' => '16:00:00'],
            ['start_time' => '16:00:00', 'end_time' => '17:00:00'],
        ];

        // Seed slots for today and the next 14 days
        $startDate = Carbon::today();
        
        for ($i = 0; $i <= 14; $i++) {
            $currentDate = $startDate->copy()->addDays($i)->format('Y-m-d');
            
            foreach ($dailySlots as $slot) {
                Slot::updateOrCreate(
                    [
                        'date' => $currentDate,
                        'start_time' => $slot['start_time'],
                    ],
                    [
                        'end_time' => $slot['end_time'],
                        'max_bookings' => 3, // Allow up to 3 bookings per time slot
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('Slots generated successfully for the next 14 days!');
    }
}
