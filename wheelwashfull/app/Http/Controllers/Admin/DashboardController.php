<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ServiceCity;
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
        $cityId = $request->input('service_city_id');
        $bookingScope = Booking::query()->when($cityId, fn ($query) => $query->where('service_city_id', $cityId));
        $userScope = User::query()->when($cityId, fn ($query) => $query->where('service_city_id', $cityId));

        // Dashboard statistics
        $stats = [
            'total_bookings' => (clone $bookingScope)->count(),
            'today_bookings' => (clone $bookingScope)->whereDate('booking_date', Carbon::today())->count(),
            'pending_bookings' => (clone $bookingScope)->where('status', 'pending')->count(),
            'completed_bookings' => (clone $bookingScope)->where('status', 'completed')->count(),
            'total_revenue' => (clone $bookingScope)->where('payment_status', 'paid')->sum('final_price'),
            'cod_amount' => (clone $bookingScope)->where('payment_method', 'cod')->where('payment_status', 'paid')->sum('final_price'),
            'online_paid_amount' => (clone $bookingScope)->where('payment_method', 'online')->where('payment_status', 'paid')->sum('final_price'),
            'total_customers' => (clone $userScope)->where('role', 'customer')->count(),
            'total_partners' => (clone $userScope)->where('role', 'partner')->count(),
            'total_workers' => (clone $userScope)->where('role', 'worker')->count(),
            'total_pickup_drivers' => (clone $userScope)->where('role', 'pickup_driver')->count(),
            'total_city_admins' => User::where('role', 'city_admin')->when($cityId, fn ($query) => $query->where('service_city_id', $cityId))->count(),
        ];

        // Recent bookings
        $recentBookings = Booking::with(['user', 'service', 'partner'])
            ->when($cityId, fn ($query) => $query->where('service_city_id', $cityId))
            ->latest()
            ->take(10)
            ->get();

        // Chart data - bookings per day (last 7 days)
        $bookingsPerDay = Booking::selectRaw('DATE(booking_date) as date, COUNT(*) as count')
            ->when($cityId, fn ($query) => $query->where('service_city_id', $cityId))
            ->whereBetween('booking_date', [Carbon::now()->subDays(7), Carbon::now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Chart data - revenue per day (last 7 days)
        $revenuePerDay = Booking::selectRaw('DATE(booking_date) as date, SUM(final_price) as total')
            ->when($cityId, fn ($query) => $query->where('service_city_id', $cityId))
            ->where('payment_status', 'paid')
            ->whereBetween('booking_date', [Carbon::now()->subDays(7), Carbon::now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $cityWise = ServiceCity::orderBy('sort_order')->orderBy('name')->get()->map(function (ServiceCity $city) {
            $bookings = Booking::where('service_city_id', $city->id);

            return [
                'city' => $city,
                'bookings' => (clone $bookings)->count(),
                'revenue' => (clone $bookings)->where('payment_status', 'paid')->sum('final_price'),
                'team' => User::where('service_city_id', $city->id)->whereIn('role', ['partner', 'worker', 'pickup_driver'])->count(),
            ];
        });

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

        return view('admin.dashboard.index', compact('stats', 'recentBookings', 'bookingsPerDay', 'revenuePerDay', 'cityWise'));
    }
}
