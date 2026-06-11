<?php

namespace App\Services;

use App\Models\Rating;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RatingService
{
    /**
     * Create a rating for a booking
     *
     * @param array $data
     * @return array
     */
    public function createRating(array $data): array
    {
        $booking = Booking::find($data['booking_id']);

        if (!$booking) {
            return [
                'success' => false,
                'message' => 'Booking not found',
            ];
        }

        // Validate that the user is the booking owner
        if ($booking->user_id !== $data['user_id']) {
            return [
                'success' => false,
                'message' => 'You can only rate your own bookings',
            ];
        }

        // Validate that booking is completed
        if ($booking->status !== 'completed') {
            return [
                'success' => false,
                'message' => 'You can only rate completed bookings',
            ];
        }

        // Check if rating already exists
        $existingRating = Rating::where('booking_id', $booking->id)->first();
        if ($existingRating) {
            return [
                'success' => false,
                'message' => 'You have already rated this booking',
            ];
        }

        // Create the rating
        $rating = Rating::create([
            'booking_id' => $booking->id,
            'user_id' => $data['user_id'],
            'partner_id' => $booking->partner_id,
            'rating' => $data['rating'],
            'review' => $data['review'] ?? null,
        ]);

        // Load relationships
        $rating->load(['booking.service', 'user', 'partner']);

        return [
            'success' => true,
            'message' => 'Rating submitted successfully',
            'data' => $rating,
        ];
    }

    /**
     * Update an existing rating
     *
     * @param Rating $rating
     * @param array $data
     * @param int $userId
     * @return array
     */
    public function updateRating(Rating $rating, array $data, int $userId): array
    {
        // Validate that the user is the rating owner
        if ($rating->user_id !== $userId) {
            return [
                'success' => false,
                'message' => 'You can only update your own ratings',
            ];
        }

        $rating->update([
            'rating' => $data['rating'] ?? $rating->rating,
            'review' => $data['review'] ?? $rating->review,
        ]);

        $rating->load(['booking.service', 'user', 'partner']);

        return [
            'success' => true,
            'message' => 'Rating updated successfully',
            'data' => $rating,
        ];
    }

    /**
     * Delete a rating
     *
     * @param Rating $rating
     * @param int $userId
     * @return array
     */
    public function deleteRating(Rating $rating, int $userId): array
    {
        // Validate that the user is the rating owner
        if ($rating->user_id !== $userId) {
            return [
                'success' => false,
                'message' => 'You can only delete your own ratings',
            ];
        }

        $rating->delete();

        return [
            'success' => true,
            'message' => 'Rating deleted successfully',
        ];
    }

    /**
     * Get ratings for a partner with average
     *
     * @param int $partnerId
     * @return array
     */
    public function getPartnerRatings(int $partnerId): array
    {
        $ratings = Rating::with(['booking.service', 'user'])
            ->forPartner($partnerId)
            ->recent()
            ->get();

        $averageRating = $ratings->avg('rating');
        $totalRatings = $ratings->count();

        $ratingDistribution = [
            5 => $ratings->where('rating', 5)->count(),
            4 => $ratings->where('rating', 4)->count(),
            3 => $ratings->where('rating', 3)->count(),
            2 => $ratings->where('rating', 2)->count(),
            1 => $ratings->where('rating', 1)->count(),
        ];

        return [
            'success' => true,
            'data' => [
                'ratings' => $ratings,
                'average_rating' => round($averageRating, 2),
                'total_ratings' => $totalRatings,
                'rating_distribution' => $ratingDistribution,
            ],
        ];
    }

    /**
     * Get ratings by a customer
     *
     * @param int $userId
     * @return array
     */
    public function getCustomerRatings(int $userId): array
    {
        $ratings = Rating::with(['booking.service', 'partner'])
            ->byUser($userId)
            ->recent()
            ->get();

        return [
            'success' => true,
            'data' => $ratings,
        ];
    }

    /**
     * Get all ratings (admin)
     *
     * @return array
     */
    public function getAllRatings(): array
    {
        $ratings = Rating::with(['booking.service', 'user', 'partner'])
            ->recent()
            ->get();

        $statistics = [
            'total_ratings' => $ratings->count(),
            'average_rating' => round($ratings->avg('rating'), 2),
            'total_reviews' => $ratings->whereNotNull('review')->count(),
        ];

        return [
            'success' => true,
            'data' => [
                'ratings' => $ratings,
                'statistics' => $statistics,
            ],
        ];
    }

    /**
     * Get rating for a specific booking
     *
     * @param int $bookingId
     * @return array
     */
    public function getBookingRating(int $bookingId): array
    {
        $rating = Rating::with(['user', 'partner'])
            ->where('booking_id', $bookingId)
            ->first();

        if (!$rating) {
            return [
                'success' => false,
                'message' => 'No rating found for this booking',
            ];
        }

        return [
            'success' => true,
            'data' => $rating,
        ];
    }

    /**
     * Calculate and update partner average rating
     * This can be called periodically or after each rating
     *
     * @param int $partnerId
     * @return float
     */
    public function calculatePartnerAverageRating(int $partnerId): float
    {
        $average = Rating::where('partner_id', $partnerId)->avg('rating');
        return round($average ?? 0, 2);
    }

    /**
     * Get top rated partners
     *
     * @param int $limit
     * @return array
     */
    public function getTopRatedPartners(int $limit = 10): array
    {
        $topPartners = Rating::select('partner_id', DB::raw('AVG(rating) as average_rating'), DB::raw('COUNT(*) as total_ratings'))
            ->groupBy('partner_id')
            ->having('total_ratings', '>=', 5) // At least 5 ratings
            ->orderBy('average_rating', 'desc')
            ->limit($limit)
            ->with('partner')
            ->get();

        return [
            'success' => true,
            'data' => $topPartners,
        ];
    }
}
