<?php

namespace App\Http\Controllers\Api\Customer;

use App\Constants\ServiceMode;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\CustomerSubscription;
use App\Models\Service;
use App\Models\Slot;
use App\Models\SubscriptionBooking;
use App\Models\SubscriptionPlan;
use App\Services\BookingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    public function plans(Request $request)
    {
        $request->validate([
            'service_city_id' => ['nullable', 'exists:service_cities,id'],
            'service_zone_id' => ['nullable', 'exists:service_zones,id'],
        ]);

        $plans = SubscriptionPlan::with(['serviceCity', 'serviceZone'])
            ->where('status', 'active')
            ->where(function (Builder $query) use ($request) {
                $query->where('is_global', true);
                if ($request->filled('service_city_id')) {
                    $query->orWhere('service_city_id', $request->service_city_id);
                }
                if ($request->filled('service_zone_id')) {
                    $query->orWhere('service_zone_id', $request->service_zone_id);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return response()->json(['success' => true, 'data' => $plans]);
    }

    public function purchase(Request $request)
    {
        $data = $request->validate([
            'subscription_plan_id' => ['required', 'exists:subscription_plans,id'],
            'service_city_id' => ['required', 'exists:service_cities,id'],
            'service_zone_id' => ['nullable', 'exists:service_zones,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'address_id' => ['nullable', 'exists:addresses,id'],
            'payment_method' => ['nullable', 'in:cod,online'],
        ]);

        $plan = SubscriptionPlan::where('status', 'active')->findOrFail($data['subscription_plan_id']);
        $this->ensurePlanMatchesSelection($plan, (int) $data['service_city_id'], $data['service_zone_id'] ?? null);

        $paymentStatus = ($data['payment_method'] ?? 'cod') === 'cod' ? 'paid' : 'pending';
        $status = $paymentStatus === 'paid' ? 'active' : 'pending';
        $start = today();

        $subscription = CustomerSubscription::create([
            'user_id' => auth()->id(),
            'subscription_plan_id' => $plan->id,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'customer_address_id' => $data['address_id'] ?? null,
            'service_city_id' => (int) $data['service_city_id'],
            'service_zone_id' => $data['service_zone_id'] ?? null,
            'service_area' => $plan->service_area,
            'start_date' => $start,
            'end_date' => $start->copy()->addDays($plan->duration_days),
            'total_washes' => $plan->total_washes,
            'used_washes' => 0,
            'remaining_washes' => $plan->total_washes,
            'exterior_remaining' => $plan->exterior_washes,
            'interior_remaining' => $plan->interior_washes,
            'foam_remaining' => $plan->foam_washes,
            'payment_status' => $paymentStatus,
            'status' => $status,
        ]);

        return response()->json(['success' => true, 'data' => $subscription->load(['subscriptionPlan', 'serviceCity', 'serviceZone'])], 201);
    }

    public function mine()
    {
        return response()->json([
            'success' => true,
            'data' => CustomerSubscription::with(['subscriptionPlan', 'serviceCity', 'serviceZone', 'vehicle'])
                ->where('user_id', auth()->id())
                ->latest()
                ->get(),
        ]);
    }

    public function show(CustomerSubscription $subscription)
    {
        abort_unless($subscription->user_id === auth()->id(), 403);

        return response()->json([
            'success' => true,
            'data' => $subscription->load(['subscriptionPlan', 'serviceCity', 'serviceZone', 'vehicle', 'subscriptionBookings.booking']),
        ]);
    }

    public function bookWash(Request $request, CustomerSubscription $subscription, BookingService $bookingService)
    {
        abort_unless($subscription->user_id === auth()->id(), 403);

        $data = $request->validate([
            'wash_type' => ['required', 'in:exterior,interior,foam'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_slot_id' => ['required'],
            'service_mode' => ['required', 'in:doorstep,pickup_drop'],
            'address_id' => ['required', 'exists:addresses,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->ensureCanBook($subscription, $data);
        $address = Address::where('id', $data['address_id'])->where('user_id', auth()->id())->firstOrFail();
        $service = $this->serviceForWash($data, $subscription);
        $slotTime = $this->slotTime($data['preferred_slot_id']);

        $booking = DB::transaction(function () use ($bookingService, $subscription, $data, $address, $service, $slotTime) {
            $booking = $bookingService->createBooking([
                'user_id' => auth()->id(),
                'vehicle_id' => $data['vehicle_id'],
                'service_id' => $service->id,
                'service_city_id' => $subscription->service_city_id,
                'service_zone_id' => $subscription->service_zone_id,
                'service_mode' => $data['service_mode'] === 'pickup_drop' ? ServiceMode::PICKUP_DROP : ServiceMode::DOORSTEP,
                'booking_date' => $data['preferred_date'],
                'slot_time' => $slotTime,
                'address' => $address->full_address,
                'latitude' => $address->latitude,
                'longitude' => $address->longitude,
                'payment_method' => 'cod',
                'address_id' => $address->id,
                'notes' => $data['notes'] ?? null,
                'booking_source' => 'subscription',
                'customer_subscription_id' => $subscription->id,
                'subscription_wash_type' => $data['wash_type'],
            ]);

            $booking->forceFill([
                'booking_source' => 'subscription',
                'customer_subscription_id' => $subscription->id,
                'subscription_wash_type' => $data['wash_type'],
            ])->save();

            SubscriptionBooking::create([
                'customer_subscription_id' => $subscription->id,
                'booking_id' => $booking->id,
                'wash_type' => $data['wash_type'],
                'status' => 'reserved',
            ]);

            return $booking->fresh(['service', 'vehicle']);
        });

        return response()->json(['success' => true, 'data' => $booking], 201);
    }

    private function ensurePlanMatchesSelection(SubscriptionPlan $plan, int $cityId, mixed $zoneId): void
    {
        if ($plan->is_global) {
            return;
        }

        if ((int) $plan->service_city_id !== $cityId) {
            throw ValidationException::withMessages(['subscription_plan_id' => 'Selected plan does not belong to this city.']);
        }

        if ($plan->service_zone_id && (int) $plan->service_zone_id !== (int) $zoneId) {
            throw ValidationException::withMessages(['service_zone_id' => 'Selected plan requires the matching zone.']);
        }
    }

    private function ensureCanBook(CustomerSubscription $subscription, array $data): void
    {
        $subscription->loadMissing('subscriptionPlan');
        if ($subscription->status !== 'active' || $subscription->payment_status !== 'paid' || $subscription->end_date?->isPast()) {
            throw ValidationException::withMessages(['subscription' => 'Subscription is not active.']);
        }
        if ($subscription->remaining_washes <= 0) {
            throw ValidationException::withMessages(['remaining_washes' => 'No washes remaining.']);
        }

        $column = $data['wash_type'].'_remaining';
        if ($subscription->{$column} <= 0) {
            throw ValidationException::withMessages(['wash_type' => 'No remaining washes for this wash type.']);
        }
        if ($data['service_mode'] === 'pickup_drop' && ! $subscription->subscriptionPlan->pickup_drop_included) {
            throw ValidationException::withMessages(['service_mode' => 'Pickup/drop is not included in this plan.']);
        }
        if ($data['service_mode'] === 'doorstep' && ! $subscription->subscriptionPlan->doorstep_included) {
            throw ValidationException::withMessages(['service_mode' => 'Doorstep service is not included in this plan.']);
        }
        if ($subscription->subscriptionPlan->max_washes_per_week) {
            $usedThisWeek = $subscription->subscriptionBookings()
                ->whereIn('status', ['reserved', 'used'])
                ->whereHas('booking', fn (Builder $query) => $query->whereBetween('booking_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]))
                ->count();
            if ($usedThisWeek >= $subscription->subscriptionPlan->max_washes_per_week) {
                throw ValidationException::withMessages(['max_washes_per_week' => 'Weekly wash limit reached.']);
            }
        }
    }

    private function serviceForWash(array $data, CustomerSubscription $subscription): Service
    {
        if (! empty($data['service_id'])) {
            $service = Service::where('id', $data['service_id'])->where('is_active', true)->where('status', 'active')->firstOrFail();
            if (! $service->is_global && (int) $service->service_city_id !== (int) $subscription->service_city_id) {
                throw ValidationException::withMessages(['service_id' => 'Selected service does not belong to subscription city.']);
            }
            return $service;
        }

        return Service::where('is_active', true)
            ->where('status', 'active')
            ->where(function (Builder $query) use ($subscription) {
                $query->where('is_global', true)->orWhere('service_city_id', $subscription->service_city_id);
            })
            ->where('name', 'like', '%'.$data['wash_type'].'%')
            ->orderBy('sort_order')
            ->first()
            ?? Service::where('is_active', true)->where('status', 'active')->where('service_city_id', $subscription->service_city_id)->firstOrFail();
    }

    private function slotTime(mixed $preferredSlotId): string
    {
        if (is_numeric($preferredSlotId)) {
            $slot = Slot::find($preferredSlotId);
            if ($slot) {
                return substr((string) $slot->start_time, 0, 5);
            }
        }

        return (string) $preferredSlotId;
    }
}
