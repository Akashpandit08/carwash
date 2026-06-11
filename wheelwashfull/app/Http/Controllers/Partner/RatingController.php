<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
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
     * Get all ratings for the authenticated partner
     */
    public function index()
    {
        $result = $this->ratingService->getPartnerRatings(auth()->id());

        return response()->json($result);
    }

    /**
     * Get partner's rating statistics
     */
    public function statistics()
    {
        $result = $this->ratingService->getPartnerRatings(auth()->id());

        return response()->json([
            'success' => true,
            'data' => [
                'average_rating' => $result['data']['average_rating'],
                'total_ratings' => $result['data']['total_ratings'],
                'rating_distribution' => $result['data']['rating_distribution'],
            ],
        ]);
    }

    /**
     * Get recent ratings
     */
    public function recent(Request $request)
    {
        $limit = $request->input('limit', 10);

        $result = $this->ratingService->getPartnerRatings(auth()->id());
        $recentRatings = $result['data']['ratings']->take($limit);

        return response()->json([
            'success' => true,
            'data' => $recentRatings,
        ]);
    }
}
