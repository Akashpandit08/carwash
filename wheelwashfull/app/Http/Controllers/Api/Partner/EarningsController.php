<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Constants\BookingStatus;

class EarningsController extends Controller
{
    public function index()
    {
        $partnerId = auth()->id();
        $completedBookings = Booking::where('partner_id', $partnerId)
            ->where('status', BookingStatus::COMPLETED)
            ->get();

        $totalEarnings = $completedBookings->sum('total_amount');
        $todayEarnings = $completedBookings->where('updated_at', '>=', now()->startOfDay())->sum('total_amount');
        $weekEarnings = $completedBookings->where('updated_at', '>=', now()->startOfWeek())->sum('total_amount');

        // Simple mock for payouts
        $pendingPayout = 0;
        $paidPayout = 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_earnings' => $totalEarnings,
                'today_earnings' => $todayEarnings,
                'week_earnings' => $weekEarnings,
                'pending_payout' => $pendingPayout,
                'paid_payout' => $paidPayout,
                'cash_collected' => $completedBookings->where('payment_status', 'paid')->where('payment_mode', 'cash')->sum('total_amount'),
                'admin_collected' => $completedBookings->where('payment_status', 'paid')->where('payment_mode', '!=', 'cash')->sum('total_amount'),
            ]
        ]);
    }

    public function ledger()
    {
        $partnerId = auth()->id();
        $completedBookings = Booking::where('partner_id', $partnerId)
            ->where('status', BookingStatus::COMPLETED)
            ->latest()
            ->paginate(15);

        $transactions = collect($completedBookings->items())->map(function ($booking) {
            return [
                'id' => $booking->id,
                'booking_id' => $booking->id,
                'amount' => $booking->total_amount,
                'payment_mode' => $booking->payment_mode ?? 'cash',
                'commission' => $booking->total_amount * 0.1, // 10% example
                'cash_collected' => $booking->payment_mode === 'cash' ? $booking->total_amount : 0,
                'admin_collected' => $booking->payment_mode !== 'cash' ? $booking->total_amount : 0,
                'payout_status' => 'pending',
                'date' => $booking->updated_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'meta' => [
                'current_page' => $completedBookings->currentPage(),
                'last_page' => $completedBookings->lastPage(),
                'total' => $completedBookings->total(),
            ]
        ]);
    }

    public function requestPayout(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payout request submitted successfully.',
        ]);
    }
}
