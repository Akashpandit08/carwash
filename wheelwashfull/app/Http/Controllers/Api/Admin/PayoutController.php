<?php

namespace App\Http\Controllers\Api\Admin;

use App\Constants\PayoutStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PayoutResource;
use App\Models\Payout;

class PayoutController extends Controller
{
    public function index()
    {
        return PayoutResource::collection(Payout::with('user', 'booking')->latest()->paginate(request('per_page', 15)))
            ->additional(['success' => true]);
    }

    public function approve(Payout $payout)
    {
        $payout->update(['payout_status' => PayoutStatus::APPROVED]);

        return response()->json(['success' => true, 'data' => new PayoutResource($payout->load('user'))]);
    }

    public function markPaid(Payout $payout)
    {
        $payout->update(['payout_status' => PayoutStatus::PAID, 'paid_at' => now()]);

        return response()->json(['success' => true, 'data' => new PayoutResource($payout->load('user'))]);
    }
}
