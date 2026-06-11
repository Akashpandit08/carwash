<?php

namespace App\Http\Controllers\Api\Admin;

use App\Constants\BookingStatus;
use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;

class ReportController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();

        return response()->json([
            'success' => true,
            'data' => [
                'bookings' => [
                    'total' => Booking::count(),
                    'today' => Booking::where('created_at', '>=', $today)->count(),
                    'pending' => Booking::where('status', BookingStatus::PENDING)->count(),
                    'completed' => Booking::where('status', BookingStatus::COMPLETED)->count(),
                    'cancelled' => Booking::where('status', BookingStatus::CANCELLED)->count(),
                ],
                'revenue' => [
                    'total' => (float) Booking::where('status', BookingStatus::COMPLETED)->sum('total_amount'),
                    'today' => (float) Booking::where('status', BookingStatus::COMPLETED)->where('updated_at', '>=', $today)->sum('total_amount'),
                ],
                'users' => [
                    'customers' => User::where('role', UserRole::CUSTOMER)->count(),
                    'partners' => User::where('role', UserRole::PARTNER)->count(),
                    'workers' => User::where('role', UserRole::WORKER)->count(),
                    'pickup_drivers' => User::where('role', UserRole::PICKUP_DRIVER)->count(),
                ],
                'recent_bookings' => Booking::with(['user', 'service'])->latest()->take(5)->get(),
            ],
        ]);
    }
}
