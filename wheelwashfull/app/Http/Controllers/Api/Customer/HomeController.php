<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\AppBanner;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Get data for the unified Home Feed.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Get user summary
        $userSummary = [
            'id' => $user->id,
            'name' => $user->name,
            'default_address' => $user->addresses()->where('is_default', true)->first() 
                ?? $user->addresses()->first()
        ];

        // 2. Get featured services (limit 4 for the home screen)
        $featuredServices = Service::where('is_active', true)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $banners = AppBanner::where('is_active', true)
            ->where('position', 'home_top')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        // 3. Get active booking (if any) to display the banner
        $activeBooking = Booking::with('service')
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed', 'assigned', 'on_the_way', 'in_progress'])
            ->latest()
            ->first();

        // Return combined JSON response
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $userSummary,
                'banners' => $banners,
                'featured_services' => $featuredServices,
                'active_booking' => $activeBooking ? [
                    'id' => $activeBooking->id,
                    'service_name' => $activeBooking->service->name ?? 'Service',
                    'status' => $activeBooking->status,
                    'booking_date' => $activeBooking->booking_date,
                    'slot_time' => $activeBooking->slot_time,
                ] : null
            ]
        ]);
    }
}
