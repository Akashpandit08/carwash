<?php

namespace App\Services;

use App\Constants\MediaType;
use App\Models\Booking;
use App\Models\BookingMedia;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class MediaUploadService
{
    public function upload(Booking $booking, User $uploader, UploadedFile $file, string $type, ?string $side = null): BookingMedia
    {
        $filePath = method_exists($file, 'storeOnCloudinary')
            ? $file->storeOnCloudinary("bookings/{$booking->id}")->getSecurePath()
            : $file->store("bookings/{$booking->id}", 'public');

        return BookingMedia::create([
            'booking_id' => $booking->id,
            'uploaded_by_user_id' => $uploader->id,
            'type' => $type,
            'side' => $side,
            'file_path' => $filePath,
        ]);
    }

    public function hasMedia(Booking $booking, string $type): bool
    {
        return $booking->media()->where('type', $type)->exists();
    }

    public function mediaCount(Booking $booking, string $type): int
    {
        return $booking->media()->where('type', $type)->count();
    }

    public function assertCanStatus(Booking $booking, string $newStatus): void
    {
        $requirements = [
            'car_picked_up' => MediaType::PICKUP_PROOF,
            'delivered' => MediaType::DELIVERY_PROOF,
        ];

        if ($newStatus === 'service_started' && $booking->worker_id) {
            $requirements[$newStatus] = MediaType::BEFORE_IMAGE;
        }

        if ($newStatus === 'service_completed') {
            $requirements[$newStatus] = $booking->worker_id ? MediaType::AFTER_IMAGE : MediaType::PARTNER_SERVICE_PROOF;
        }

        if (isset($requirements[$newStatus]) && $this->mediaCount($booking, $requirements[$newStatus]) < 4) {
            abort(422, "Missing required media: {$requirements[$newStatus]}. Minimum 4 photos required.");
        }
    }
}
