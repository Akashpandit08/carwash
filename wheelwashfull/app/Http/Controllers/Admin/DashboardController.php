<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function index(Request $request)
    {
        // Dashboard statistics
        $stats = [
            'total_bookings' => Booking::count(),
            'today_bookings' => Booking::whereDate('booking_date', Carbon::today())->count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'completed_bookings' => Booking::where('status', 'completed')->count(),
            'total_revenue' => Booking::where('payment_status', 'paid')->sum('final_price'),
            'cod_amount' => Booking::where('payment_method', 'cod')->where('payment_status', 'paid')->sum('final_price'),
            'online_paid_amount' => Booking::where('payment_method', 'online')->where('payment_status', 'paid')->sum('final_price'),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_partners' => User::where('role', 'partner')->count(),
        ];

        // Recent bookings
        $recentBookings = Booking::with(['user', 'service', 'partner'])
            ->latest()
            ->take(10)
            ->get();

        // Chart data - bookings per day (last 7 days)
        $bookingsPerDay = Booking::selectRaw('DATE(booking_date) as date, COUNT(*) as count')
            ->whereBetween('booking_date', [Carbon::now()->subDays(7), Carbon::now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Chart data - revenue per day (last 7 days)
        $revenuePerDay = Booking::selectRaw('DATE(booking_date) as date, SUM(final_price) as total')
            ->where('payment_status', 'paid')
            ->whereBetween('booking_date', [Carbon::now()->subDays(7), Carbon::now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_bookings' => $recentBookings,
                    'bookings_per_day' => $bookingsPerDay,
                    'revenue_per_day' => $revenuePerDay,
                ],
            ]);
        }

        return view('admin.dashboard.index', compact('stats', 'recentBookings', 'bookingsPerDay', 'revenuePerDay'));
    }
}
