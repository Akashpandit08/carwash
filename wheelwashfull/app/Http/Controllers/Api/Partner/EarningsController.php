<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Constants\BookingStatus;
use App\Models\Payout;

class EarningsController extends Controller
{
    public function index()
    {
        $partnerId = auth()->id();
        $payouts = Payout::with('booking')->where('user_id', $partnerId)->where('role', 'partner')->latest()->get();
        $completedBookings = Booking::where('partner_id', $partnerId)->where('status', BookingStatus::COMPLETED)->latest()->get();
        $transactions = $payouts->isNotEmpty()
            ? $payouts->map(fn ($payout) => [
                'id' => $payout->id,
                'booking_id' => $payout->booking_id,
                'amount' => (float) $payout->net_amount,
                'status' => $payout->payout_status,
                'date' => $payout->created_at->toDateString(),
            ])
            : $completedBookings->map(fn ($booking) => [
                'id' => $booking->id,
                'booking_id' => $booking->id,
                'amount' => round((float) ($booking->total_amount ?? 0) * 0.55, 2),
                'status' => 'pending',
                'date' => $booking->updated_at->toDateString(),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'total_earnings' => round($transactions->sum('amount'), 2),
                'today_earnings' => round($transactions->filter(fn ($item) => $item['date'] >= now()->toDateString())->sum('amount'), 2),
                'week_earnings' => round($transactions->filter(fn ($item) => $item['date'] >= now()->startOfWeek()->toDateString())->sum('amount'), 2),
                'pending_earnings' => round($transactions->whereIn('status', ['pending', 'approved'])->sum('amount'), 2),
                'paid_earnings' => round($transactions->where('status', 'paid')->sum('amount'), 2),
                'pending_payout' => round($transactions->whereIn('status', ['pending', 'approved'])->sum('amount'), 2),
                'paid_payout' => round($transactions->where('status', 'paid')->sum('amount'), 2),
                'transactions' => $transactions->values(),
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
