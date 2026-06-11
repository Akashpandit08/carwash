<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Services\RatingService;

class ReviewController extends Controller
{
    public function __construct(
        protected RatingService $ratingService
    ) {}

    /**
     * Show the partner's ratings & reviews dashboard.
     */
    public function index()
    {
        $partnerId = auth()->id();
        $result    = $this->ratingService->getPartnerRatings($partnerId);

        $ratings             = $result['data']['ratings'];
        $averageRating       = $result['data']['average_rating'];
        $totalRatings        = $result['data']['total_ratings'];
        $ratingDistribution  = $result['data']['rating_distribution'];

        return view('partner.reviews.index', compact(
            'ratings',
            'averageRating',
            'totalRatings',
            'ratingDistribution'
        ));
    }
}
