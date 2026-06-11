<?php

namespace Database\Seeders;

use App\Models\NotificationSound;
use Illuminate\Database\Seeder;

class NotificationSoundSeeder extends Seeder
{
    /**
     * Seed dummy notification sounds.
     *
     * Sound files should be placed in: storage/app/public/sounds/
     * After placing files, run: php artisan storage:link
     * This makes them accessible at: {APP_URL}/storage/sounds/filename.mp3
     */
    public function run(): void
    {
        $sounds = [
            [
                'name' => 'default',
                'file_path' => 'sounds/default-notification.mp3',
                'is_default' => true,
            ],
            [
                'name' => 'booking_alert',
                'file_path' => 'sounds/booking-alert.mp3',
                'is_default' => false,
            ],
            [
                'name' => 'urgent',
                'file_path' => 'sounds/urgent-notification.mp3',
                'is_default' => false,
            ],
        ];

        foreach ($sounds as $sound) {
            NotificationSound::updateOrCreate(
                ['name' => $sound['name']],
                $sound
            );
        }

        // Note: Place actual .mp3 files in storage/app/public/sounds/
        // Then run: php artisan storage:link
        // Files will be accessible at: {APP_URL}/storage/sounds/filename.mp3
    }
}
