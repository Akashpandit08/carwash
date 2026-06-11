<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Rating::with(['booking.service', 'booking.user', 'booking.partner', 'user', 'partner']);

        if ($request->filled('search')) {
            $query->where('review', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reviews = $query->latest()->paginate(20)->withQueryString();

        // Summary stats (always across ALL reviews, not filtered)
        $totalReviews     = Rating::count();
        $averageRating    = round(Rating::avg('rating') ?? 0, 1);
        $ratingDistribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingDistribution[$i] = Rating::where('rating', $i)->count();
        }

        return view('admin.reviews.index', compact('reviews', 'totalReviews', 'averageRating', 'ratingDistribution'));
    }

    public function show(Rating $review)
    {
        $rating = $review->load(['booking.service', 'booking.user', 'booking.partner']);
        return view('admin.reviews.show', compact('rating'));
    }

    public function destroy(Rating $review)
    {
        $review->delete();
        return back()->with('success', 'Review deleted successfully.');
    }
}
