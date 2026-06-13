<?php

namespace App\Http\Controllers\Api\Admin;

use App\Constants\BookingStatus;
use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Services\CityScopeService;

class ReportController extends Controller
{
    public function index(CityScopeService $cityScope)
    {
        $today = now()->startOfDay();
        $user = auth()->user();
        $bookings = Booking::query();
        $cityScope->apply($bookings, $user);
        $users = User::query();
        $cityScope->apply($users, $user);

        return response()->json([
            'success' => true,
            'data' => [
                'bookings' => [
                    'total' => (clone $bookings)->count(),
                    'today' => (clone $bookings)->where('created_at', '>=', $today)->count(),
                    'pending' => (clone $bookings)->where('status', BookingStatus::PENDING)->count(),
                    'completed' => (clone $bookings)->where('status', BookingStatus::COMPLETED)->count(),
                    'cancelled' => (clone $bookings)->where('status', BookingStatus::CANCELLED)->count(),
                ],
                'revenue' => [
                    'total' => (float) (clone $bookings)->where('status', BookingStatus::COMPLETED)->sum('total_amount'),
                    'today' => (float) (clone $bookings)->where('status', BookingStatus::COMPLETED)->where('updated_at', '>=', $today)->sum('total_amount'),
                ],
                'users' => [
                    'customers' => (clone $users)->where('role', UserRole::CUSTOMER)->count(),
                    'partners' => (clone $users)->where('role', UserRole::PARTNER)->count(),
                    'workers' => (clone $users)->where('role', UserRole::WORKER)->count(),
                    'pickup_drivers' => (clone $users)->where('role', UserRole::PICKUP_DRIVER)->count(),
                ],
                'recent_bookings' => (clone $bookings)->with(['user', 'service'])->latest()->take(5)->get(),
            ],
        ]);
    }
}
