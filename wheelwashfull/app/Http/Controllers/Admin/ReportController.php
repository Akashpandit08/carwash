<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Models\Rating;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(\App\Services\ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Show reports dashboard
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'date_from', 'date_to', 'partner_id', 'service_id', 'status', 'payment_status', 'service_city_id'
        ]);

        // Default to last 30 days if no date provided
        if (empty($filters['date_from']) && empty($filters['date_to'])) {
            $filters['date_from'] = Carbon::now()->subDays(30)->toDateString();
            $filters['date_to'] = Carbon::now()->toDateString();
        }

        $stats = $this->reportService->getSummaryStats($filters);
        $statusReport = $this->reportService->getBookingsByStatus($filters);
        $dailyBookings = $this->reportService->getDailyBookings($filters);
        $monthlyBookings = $this->reportService->getMonthlyBookings($filters);
        $partnerPerformance = $this->reportService->getPartnerPerformance($filters);
        $servicePerformance = $this->reportService->getServicePerformance($filters);
        $couponUsage = $this->reportService->getCouponUsage($filters);

        // Fetch options for filter dropdowns
        $partners = User::where('role', 'partner')
            ->when($request->filled('service_city_id'), fn ($query) => $query->where('service_city_id', $request->service_city_id))
            ->select('id', 'name')
            ->get();
        $services = \App\Models\Service::when($request->filled('service_city_id'), fn ($query) => $query->where('service_city_id', $request->service_city_id))
            ->select('id', 'name')
            ->get();

        return view('admin.reports.index', compact(
            'filters',
            'stats',
            'statusReport',
            'dailyBookings',
            'monthlyBookings',
            'partnerPerformance',
            'servicePerformance',
            'couponUsage',
            'partners',
            'services'
        ));
    }
}
