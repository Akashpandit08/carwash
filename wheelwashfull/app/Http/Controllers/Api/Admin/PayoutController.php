<?php

namespace App\Http\Controllers\Api\Admin;

use App\Constants\PayoutStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PayoutResource;
use App\Models\Payout;
use App\Services\CityScopeService;

class PayoutController extends Controller
{
    public function index(CityScopeService $cityScope)
    {
        $query = Payout::with('user', 'booking');

        if (! $cityScope->isSuperAdmin(auth()->user())) {
            $query->whereHas('booking', fn ($booking) => $booking->where('service_city_id', auth()->user()->service_city_id));
        } elseif (request('service_city_id')) {
            $query->whereHas('booking', fn ($booking) => $booking->where('service_city_id', request('service_city_id')));
        }

        return PayoutResource::collection($query->latest()->paginate(request('per_page', 15)))
            ->additional(['success' => true]);
    }

    public function approve(Payout $payout, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $payout->booking);

        $payout->update(['payout_status' => PayoutStatus::APPROVED]);

        return response()->json(['success' => true, 'data' => new PayoutResource($payout->load('user'))]);
    }

    public function reject(Payout $payout, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $payout->booking);

        $payout->update(['payout_status' => PayoutStatus::REJECTED]);

        return response()->json(['success' => true, 'data' => new PayoutResource($payout->load('user'))]);
    }

    public function markPaid(Payout $payout, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $payout->booking);

        $payout->update(['payout_status' => PayoutStatus::PAID, 'paid_at' => now()]);

        return response()->json(['success' => true, 'data' => new PayoutResource($payout->load('user'))]);
    }
}
