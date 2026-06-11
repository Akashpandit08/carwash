<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'service'])->where('status', 'completed');

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $payments = $query->orderBy('updated_at', 'desc')->paginate(20);

        $totalRevenue = Booking::where('payment_status', 'paid')->sum('final_price') ?? 0;
        $codAmount = Booking::where('payment_status', 'paid')->where('payment_method', 'cod')->sum('final_price') ?? 0;
        $onlineAmount = Booking::where('payment_status', 'paid')->where('payment_method', 'online')->sum('final_price') ?? 0;
        $pendingAmount = Booking::where('payment_status', 'pending')->sum('final_price') ?? 0;

        return view('admin.payments.index', compact('payments', 'totalRevenue', 'codAmount', 'onlineAmount', 'pendingAmount'));
    }
}
