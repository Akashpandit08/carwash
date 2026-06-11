<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
     * Get all ratings with statistics
     */
    public function index()
    {
        $result = $this->ratingService->getAllRatings();

        return response()->json($result);
    }

    /**
     * Get a specific rating
     */
    public function show(Rating $rating)
    {
        $rating->load(['booking.service', 'user', 'partner']);

        return response()->json([
            'success' => true,
            'data' => $rating,
        ]);
    }

    /**
     * Get ratings for a specific partner
     */
    public function partnerRatings(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:users,id',
        ]);

        $result = $this->ratingService->getPartnerRatings($request->partner_id);

        return response()->json($result);
    }

    /**
     * Get ratings by a specific customer
     */
    public function customerRatings(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
        ]);

        $result = $this->ratingService->getCustomerRatings($request->customer_id);

        return response()->json($result);
    }

    /**
     * Get top rated partners
     */
    public function topRatedPartners(Request $request)
    {
        $limit = $request->input('limit', 10);

        $result = $this->ratingService->getTopRatedPartners($limit);

        return response()->json($result);
    }

    /**
     * Delete a rating (admin override)
     */
    public function destroy(Rating $rating)
    {
        $rating->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rating deleted successfully',
        ]);
    }

    /**
     * Get rating statistics
     */
    public function statistics()
    {
        $result = $this->ratingService->getAllRatings();

        return response()->json([
            'success' => true,
            'data' => $result['data']['statistics'],
        ]);
    }
}
