<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\WorkerProfile;
use App\Models\PickupDriverProfile;
use Carbon\Carbon;
use App\Constants\BookingStatus;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $partnerId = auth()->id();
        $today = Carbon::today();

        // 1. Today's Bookings & Earnings
        $todayBookings = Booking::where('partner_id', $partnerId)
            ->whereDate('booking_date', $today);

        $todayTotalBookings = $todayBookings->count();
        $todayEarnings = $todayBookings->where('status', BookingStatus::COMPLETED)->sum('total_amount'); // Simplified earning, actual might be commission

        // 2. Active Workers
        $workers = WorkerProfile::where('partner_id', $partnerId)->with('user')->get();
        $activeWorkers = $workers->filter(function($w) { return $w->current_status === 'online'; })->count();
        $offlineWorkers = $workers->count() - $activeWorkers;

        // 3. Active Drivers
        $drivers = PickupDriverProfile::where('partner_id', $partnerId)->with('user')->get();
        $activeDrivers = $drivers->filter(function($d) { return $d->current_status === 'online'; })->count();
        $offlineDrivers = $drivers->count() - $activeDrivers;

        // 4. Pending Actions
        $pendingWorkerAssignments = Booking::where('partner_id', $partnerId)
            ->whereIn('status', [BookingStatus::PARTNER_ASSIGNED, BookingStatus::ACCEPTED_BY_PARTNER])
            ->where('wash_type', 'door_to_door')
            ->count();

        $pickupArrivingSoon = Booking::where('partner_id', $partnerId)
            ->whereIn('status', [BookingStatus::PICKUP_DRIVER_ASSIGNED, BookingStatus::DRIVER_ON_THE_WAY, BookingStatus::CAR_PICKED_UP])
            ->whereIn('wash_type', ['pickup_wash', 'pickup_drop'])
            ->count();

        $pendingAcceptance = Booking::where('partner_id', $partnerId)
            ->where('status', BookingStatus::REACHED_PARTNER)
            ->whereIn('wash_type', ['pickup_wash', 'pickup_drop'])
            ->count();

        $recentJobs = Booking::with(['service', 'vehicle', 'user'])
            ->where('partner_id', $partnerId)
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'today_earnings' => round($todayEarnings, 2),
                'today_bookings' => $todayTotalBookings,
                'active_workers' => $activeWorkers,
                'offline_workers' => $offlineWorkers,
                'active_drivers' => $activeDrivers,
                'offline_drivers' => $offlineDrivers,
                'pending_worker_assignments' => $pendingWorkerAssignments,
                'pickup_arriving_soon' => $pickupArrivingSoon,
                'pending_acceptance' => $pendingAcceptance,
                'recent_jobs' => \App\Http\Resources\Api\BookingResource::collection($recentJobs),
            ]
        ]);
    }
}
