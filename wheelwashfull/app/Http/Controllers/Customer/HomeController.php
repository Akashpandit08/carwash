<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\Booking;

class HomeController extends Controller
{
    public function index()
    {
        $categories = ServiceCategory::with('services')
            ->where('is_active', true)
            ->get();

        $recentBookings = Booking::with(['service', 'vehicle'])
            ->where('user_id', auth()->id())
            ->latest()
            ->take(3)
            ->get();

        return view('customer.home', compact('categories', 'recentBookings'));
    }
}
