<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRatingRequest;
use App\Http\Requests\UpdateRatingRequest;
use App\Models\Rating;
use App\Services\RatingService;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    protected $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    /**
     * Get all ratings submitted by the customer
     */
    public function index()
    {
        $result = $this->ratingService->getCustomerRatings(auth()->id());

        return response()->json($result);
    }

    /**
     * Submit a new rating for a booking
     */
    public function store(StoreRatingRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $result = $this->ratingService->createRating($data);

        return response()->json(
            $result,
            $result['success'] ? 201 : 422
        );
    }

    /**
     * Get a specific rating by ID
     */
    public function show(Rating $rating)
    {
        // Check if the rating belongs to the authenticated user
        if ($rating->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $rating->load(['booking.service', 'partner']);

        return response()->json([
            'success' => true,
            'data' => $rating,
        ]);
    }

    /**
     * Update a rating
     */
    public function update(UpdateRatingRequest $request, Rating $rating)
    {
        $result = $this->ratingService->updateRating(
            $rating,
            $request->validated(),
            auth()->id()
        );

        return response()->json(
            $result,
            $result['success'] ? 200 : 403
        );
    }

    /**
     * Delete a rating
     */
    public function destroy(Rating $rating)
    {
        $result = $this->ratingService->deleteRating($rating, auth()->id());

        return response()->json(
            $result,
            $result['success'] ? 200 : 403
        );
    }

    /**
     * Get rating for a specific booking
     */
    public function getByBooking(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $result = $this->ratingService->getBookingRating($request->booking_id);

        return response()->json(
            $result,
            $result['success'] ? 200 : 404
        );
    }
}
