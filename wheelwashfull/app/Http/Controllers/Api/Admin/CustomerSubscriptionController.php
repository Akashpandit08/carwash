<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use App\Models\SubscriptionPlan;
use App\Services\CityScopeService;
use Illuminate\Http\Request;

class CustomerSubscriptionController extends Controller
{
    public function index(CityScopeService $cityScope)
    {
        $query = CustomerSubscription::with(['user', 'subscriptionPlan', 'serviceCity', 'serviceZone', 'vehicle']);
        $cityScope->apply($query, auth()->user());

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }
        if (request()->filled('payment_status')) {
            $query->where('payment_status', request('payment_status'));
        }

        return response()->json(['success' => true, 'data' => $query->latest()->paginate(request('per_page', 20))]);
    }

    public function store(Request $request, CityScopeService $cityScope)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'subscription_plan_id' => ['required', 'exists:subscription_plans,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'customer_address_id' => ['nullable', 'exists:addresses,id'],
            'service_city_id' => ['nullable', 'exists:service_cities,id'],
            'service_zone_id' => ['nullable', 'exists:service_zones,id'],
            'payment_status' => ['nullable', 'in:pending,paid,failed,refunded'],
            'status' => ['nullable', 'in:pending,active,expired,cancelled'],
            'auto_renew' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $plan = SubscriptionPlan::findOrFail($data['subscription_plan_id']);
        $cityId = $plan->is_global ? $cityScope->allowedCityId(auth()->user(), $request->service_city_id) : $plan->service_city_id;
        if (! $plan->is_global) {
            $cityScope->ensureCanAccessCity(auth()->user(), $cityId);
        }

        $subscription = CustomerSubscription::create($this->payloadFromPlan($plan, $data, $cityId, $data['service_zone_id'] ?? $plan->service_zone_id));

        return response()->json(['success' => true, 'data' => $subscription->load(['user', 'subscriptionPlan', 'serviceCity', 'serviceZone'])], 201);
    }

    public function show(CustomerSubscription $subscription, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $subscription);

        return response()->json(['success' => true, 'data' => $subscription->load(['user', 'subscriptionPlan', 'serviceCity', 'serviceZone', 'subscriptionBookings.booking'])]);
    }

    public function update(Request $request, CustomerSubscription $subscription, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $subscription);

        $data = $request->validate([
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'customer_address_id' => ['nullable', 'exists:addresses,id'],
            'payment_status' => ['nullable', 'in:pending,paid,failed,refunded'],
            'status' => ['nullable', 'in:pending,active,expired,cancelled'],
            'auto_renew' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $subscription->update($data);

        return response()->json(['success' => true, 'data' => $subscription->fresh(['user', 'subscriptionPlan', 'serviceCity', 'serviceZone'])]);
    }

    public function activate(CustomerSubscription $subscription, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $subscription);
        $start = $subscription->start_date ?? today();
        $subscription->update([
            'status' => 'active',
            'payment_status' => $subscription->payment_status === 'pending' ? 'paid' : $subscription->payment_status,
            'start_date' => $start,
            'end_date' => $subscription->end_date ?? $start->copy()->addDays($subscription->subscriptionPlan->duration_days),
        ]);

        return response()->json(['success' => true, 'data' => $subscription->fresh()]);
    }

    public function cancel(CustomerSubscription $subscription, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $subscription);
        $subscription->update(['status' => 'cancelled']);

        return response()->json(['success' => true, 'data' => $subscription->fresh()]);
    }

    public function markPaid(CustomerSubscription $subscription, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $subscription);
        $subscription->update(['payment_status' => 'paid']);

        return response()->json(['success' => true, 'data' => $subscription->fresh()]);
    }

    private function payloadFromPlan(SubscriptionPlan $plan, array $data, ?int $cityId, ?int $zoneId): array
    {
        $paymentStatus = $data['payment_status'] ?? 'pending';
        $status = $data['status'] ?? ($paymentStatus === 'paid' ? 'active' : 'pending');
        $start = $status === 'active' ? today() : null;

        return [
            'user_id' => $data['user_id'],
            'subscription_plan_id' => $plan->id,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'customer_address_id' => $data['customer_address_id'] ?? null,
            'service_city_id' => $cityId,
            'service_zone_id' => $zoneId,
            'service_area' => $plan->service_area,
            'start_date' => $start,
            'end_date' => $start?->copy()->addDays($plan->duration_days),
            'total_washes' => $plan->total_washes,
            'used_washes' => 0,
            'remaining_washes' => $plan->total_washes,
            'exterior_remaining' => $plan->exterior_washes,
            'interior_remaining' => $plan->interior_washes,
            'foam_remaining' => $plan->foam_washes,
            'payment_status' => $paymentStatus,
            'status' => $status,
            'auto_renew' => $data['auto_renew'] ?? false,
            'notes' => $data['notes'] ?? null,
        ];
    }
}
