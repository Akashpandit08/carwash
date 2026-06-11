<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get a base query with all filters applied.
     */
    public function getFilteredQuery(array $filters)
    {
        $query = Booking::query();

        if (!empty($filters['date_from'])) {
            $query->whereDate('booking_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('booking_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['partner_id'])) {
            $query->where('partner_id', $filters['partner_id']);
        }
        if (!empty($filters['service_id'])) {
            $query->where('service_id', $filters['service_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        return $query;
    }

    public function getSummaryStats(array $filters)
    {
        $query = $this->getFilteredQuery($filters);
        
        $totalBookings = (clone $query)->count();
        $totalRevenue = (clone $query)->where('payment_status', 'paid')->sum('final_price');
        $codRevenue = (clone $query)->where('payment_status', 'paid')->where('payment_method', 'cod')->sum('final_price');
        $onlineRevenue = (clone $query)->where('payment_status', 'paid')->where('payment_method', 'online')->sum('final_price');

        return [
            'total_bookings' => $totalBookings,
            'total_revenue' => $totalRevenue,
            'cod_revenue' => $codRevenue,
            'online_revenue' => $onlineRevenue,
        ];
    }

    public function getBookingsByStatus(array $filters)
    {
        return $this->getFilteredQuery($filters)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
    }

    public function getDailyBookings(array $filters)
    {
        return $this->getFilteredQuery($filters)
            ->select(DB::raw('DATE(booking_date) as date'), DB::raw('count(*) as total'), DB::raw('SUM(CASE WHEN payment_status = "paid" THEN final_price ELSE 0 END) as revenue'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();
    }

    public function getMonthlyBookings(array $filters)
    {
        $driver = DB::connection()->getDriverName();
        $yearSql = $driver === 'sqlite' ? "strftime('%Y', booking_date)" : "YEAR(booking_date)";
        $monthSql = $driver === 'sqlite' ? "strftime('%m', booking_date)" : "MONTH(booking_date)";

        return $this->getFilteredQuery($filters)
            ->select(
                DB::raw("$yearSql as year"),
                DB::raw("$monthSql as month"),
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN final_price ELSE 0 END) as revenue')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    public function getPartnerPerformance(array $filters)
    {
        return $this->getFilteredQuery($filters)
            ->whereNotNull('partner_id')
            ->select(
                'partner_id',
                DB::raw('count(*) as total_assignments'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_bookings'),
                DB::raw('SUM(CASE WHEN status = "completed" AND payment_status = "paid" THEN final_price ELSE 0 END) as total_earnings')
            )
            ->groupBy('partner_id')
            ->with(['partner' => function ($query) {
                $query->select('id', 'name');
            }])
            ->get()
            ->sortByDesc('total_earnings');
    }

    public function getServicePerformance(array $filters)
    {
        return $this->getFilteredQuery($filters)
            ->select(
                'service_id',
                DB::raw('count(*) as total_bookings'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN final_price ELSE 0 END) as total_revenue')
            )
            ->groupBy('service_id')
            ->with(['service' => function ($query) {
                $query->select('id', 'name');
            }])
            ->get()
            ->sortByDesc('total_revenue');
    }

    public function getCouponUsage(array $filters)
    {
        return $this->getFilteredQuery($filters)
            ->whereNotNull('coupon_id')
            ->select(
                'coupon_id',
                DB::raw('count(*) as usage_count'),
                DB::raw('SUM(discount) as total_discount')
            )
            ->groupBy('coupon_id')
            ->with(['coupon' => function ($query) {
                $query->select('id', 'code');
            }])
            ->get()
            ->sortByDesc('usage_count');
    }
}
