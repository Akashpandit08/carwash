<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    /**
     * Display list of partners
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'partner');

        // Search by name or mobile
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('mobile_number', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $partners = $query->paginate(20);

        return view('admin.partners.index', compact('partners'));
    }

    /**
     * Show partner details
     */
    public function show(User $partner)
    {
        // Make sure it's a partner
        if ($partner->role !== 'partner') {
            abort(404);
        }

        $partner->load('receivedRatings.booking.service');

        $assignments       = $partner->assignedBookings()->with(['user', 'service', 'vehicle'])->latest()->get();
        $totalBookings     = $partner->assignedBookings()->count();
        $completedBookings = $partner->assignedBookings()->where('status', 'completed')->count();
        $totalEarnings     = $partner->assignedBookings()->where('payment_status', 'paid')->sum('final_price');
        $ratings           = $partner->receivedRatings()->with(['booking.service', 'user'])->latest()->get();
        $avgRating         = $ratings->avg('rating') ? round($ratings->avg('rating'), 1) : null;

        return view('admin.partners.show', compact(
            'partner', 'assignments', 'totalBookings', 'completedBookings',
            'totalEarnings', 'ratings', 'avgRating'
        ));
    }
}
