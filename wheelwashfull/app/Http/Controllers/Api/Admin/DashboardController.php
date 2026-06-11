<?php

namespace App\Http\Controllers\Api\Admin;

use App\Constants\BookingStatus;
use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();

        return response()->json([
            'success' => true,
            'data' => [
                'total_bookings' => Booking::count(),
                'today_bookings' => Booking::where('created_at', '>=', $today)->count(),
                'pending_bookings' => Booking::where('status', BookingStatus::PENDING)->count(),
                'completed_bookings' => Booking::where('status', BookingStatus::COMPLETED)->count(),
                'total_revenue' => (float) Booking::where('status', BookingStatus::COMPLETED)->sum('total_amount'),
                'active_partners' => User::where('role', UserRole::PARTNER)->count(),
                'active_workers' => User::where('role', UserRole::WORKER)->count(),
                'active_drivers' => User::where('role', UserRole::PICKUP_DRIVER)->count(),
            ],
        ]);
    }
}
