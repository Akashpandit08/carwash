<?php

namespace App\Services;

use App\Constants\MediaType;
use App\Models\Booking;
use App\Models\BookingMedia;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class MediaUploadService
{
    public function upload(Booking $booking, User $uploader, UploadedFile $file, string $type): BookingMedia
    {
        $filePath = method_exists($file, 'storeOnCloudinary')
            ? $file->storeOnCloudinary("bookings/{$booking->id}")->getSecurePath()
            : $file->store("bookings/{$booking->id}", 'public');

        return BookingMedia::create([
            'booking_id' => $booking->id,
            'uploaded_by_user_id' => $uploader->id,
            'type' => $type,
            'file_path' => $filePath,
        ]);
    }

    public function hasMedia(Booking $booking, string $type): bool
    {
        return $booking->media()->where('type', $type)->exists();
    }

    public function assertCanStatus(Booking $booking, string $newStatus): void
    {
        $requirements = [
            'car_picked_up' => MediaType::PICKUP_PROOF,
            'delivered' => MediaType::DELIVERY_PROOF,
        ];

        if ($newStatus === 'service_completed') {
            $requirements[$newStatus] = $booking->worker_id ? MediaType::AFTER_IMAGE : MediaType::PARTNER_SERVICE_PROOF;
        }

        if (isset($requirements[$newStatus]) && !$this->hasMedia($booking, $requirements[$newStatus])) {
            abort(422, "Missing required media: {$requirements[$newStatus]}.");
        }
    }
}
