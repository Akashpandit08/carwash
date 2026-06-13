<?php

namespace App\Http\Controllers\Api\Admin;

use App\Constants\BookingStatus;
use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ServiceCity;
use App\Models\User;
use App\Services\CityScopeService;

class DashboardController extends Controller
{
    public function index(CityScopeService $cityScope)
    {
        $today = now()->startOfDay();
        $user = auth()->user();
        $bookings = Booking::query();
        $cityScope->apply($bookings, $user);
        $completedBookings = (clone $bookings)->where('status', BookingStatus::COMPLETED);

        $teamUsers = User::query();
        $cityScope->apply($teamUsers, $user);

        $data = [
            'total_bookings' => (clone $bookings)->count(),
            'today_bookings' => (clone $bookings)->where('created_at', '>=', $today)->count(),
            'pending_bookings' => (clone $bookings)->where('status', BookingStatus::PENDING)->count(),
            'completed_bookings' => (clone $bookings)->where('status', BookingStatus::COMPLETED)->count(),
            'total_revenue' => (float) $completedBookings->sum('total_amount'),
            'active_partners' => (clone $teamUsers)->where('role', UserRole::PARTNER)->count(),
            'active_workers' => (clone $teamUsers)->where('role', UserRole::WORKER)->count(),
            'active_drivers' => (clone $teamUsers)->where('role', UserRole::PICKUP_DRIVER)->count(),
        ];

        if ($cityScope->isSuperAdmin($user)) {
            $data['city_wise'] = ServiceCity::orderBy('sort_order')->orderBy('name')->get()->map(function (ServiceCity $city) {
                $cityBookings = Booking::where('service_city_id', $city->id);

                return [
                    'service_city_id' => $city->id,
                    'service_city_name' => $city->name,
                    'bookings' => (clone $cityBookings)->count(),
                    'revenue' => (float) (clone $cityBookings)->where('status', BookingStatus::COMPLETED)->sum('total_amount'),
                ];
            });
        }

        return response()->json(['success' => true, 'data' => $data]);
    }
}
