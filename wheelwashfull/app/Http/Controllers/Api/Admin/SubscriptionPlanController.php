<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\CityScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubscriptionPlanController extends Controller
{
    public function index(CityScopeService $cityScope)
    {
        $query = SubscriptionPlan::with(['serviceCity', 'serviceZone']);
        $user = auth()->user();

        if ($cityScope->isCityAdmin($user)) {
            $query->where('service_city_id', $user->service_city_id)->where('is_global', false);
        } elseif (request()->filled('service_city_id')) {
            $query->where('service_city_id', request('service_city_id'));
        }

        foreach (['service_zone_id', 'status'] as $filter) {
            if (request()->filled($filter)) {
                $query->where($filter, request($filter));
            }
        }
        if (request()->has('is_global')) {
            $query->where('is_global', filter_var(request('is_global'), FILTER_VALIDATE_BOOL));
        }
        if (request()->filled('price_min')) {
            $query->where('price', '>=', request('price_min'));
        }
        if (request()->filled('price_max')) {
            $query->where('price', '<=', request('price_max'));
        }

        return response()->json(['success' => true, 'data' => $query->orderBy('sort_order')->latest()->paginate(request('per_page', 20))]);
    }

    public function store(Request $request, CityScopeService $cityScope)
    {
        $data = $this->validated($request);
        $this->normalize($data, $request, $cityScope);
        $this->ensureNoDuplicate($data);

        $plan = SubscriptionPlan::create($data);

        return response()->json(['success' => true, 'data' => $plan->load(['serviceCity', 'serviceZone'])], 201);
    }

    public function show(SubscriptionPlan $subscriptionPlan, CityScopeService $cityScope)
    {
        if (! $subscriptionPlan->is_global) {
            $cityScope->ensureCanAccessModel(auth()->user(), $subscriptionPlan);
        }

        return response()->json(['success' => true, 'data' => $subscriptionPlan->load(['serviceCity', 'serviceZone'])]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan, CityScopeService $cityScope)
    {
        if (! $subscriptionPlan->is_global) {
            $cityScope->ensureCanAccessModel(auth()->user(), $subscriptionPlan);
        }

        $data = $this->validated($request);
        $this->normalize($data, $request, $cityScope);
        $this->ensureNoDuplicate($data, $subscriptionPlan->id);
        $subscriptionPlan->update($data);

        return response()->json(['success' => true, 'data' => $subscriptionPlan->fresh(['serviceCity', 'serviceZone'])]);
    }

    public function destroy(SubscriptionPlan $subscriptionPlan, CityScopeService $cityScope)
    {
        if (! $subscriptionPlan->is_global) {
            $cityScope->ensureCanAccessModel(auth()->user(), $subscriptionPlan);
        }

        $subscriptionPlan->delete();

        return response()->json(['success' => true, 'message' => 'Subscription plan deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'service_city_id' => ['nullable', 'exists:service_cities,id'],
            'service_zone_id' => ['nullable', 'exists:service_zones,id'],
            'service_area' => ['nullable', 'string', 'max:255'],
            'is_global' => ['boolean'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'exterior_washes' => ['nullable', 'integer', 'min:0'],
            'interior_washes' => ['nullable', 'integer', 'min:0'],
            'foam_washes' => ['nullable', 'integer', 'min:0'],
            'tyre_polish_included' => ['boolean'],
            'dashboard_wipe_included' => ['boolean'],
            'vacuum_included' => ['boolean'],
            'priority_booking' => ['boolean'],
            'pickup_drop_included' => ['boolean'],
            'doorstep_included' => ['boolean'],
            'max_washes_per_week' => ['nullable', 'integer', 'min:1'],
            'terms' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function normalize(array &$data, Request $request, CityScopeService $cityScope): void
    {
        $user = auth()->user();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['status'] = $data['status'] ?? 'active';
        $data['duration_days'] = $data['duration_days'] ?? 30;
        $data['exterior_washes'] = $data['exterior_washes'] ?? 0;
        $data['interior_washes'] = $data['interior_washes'] ?? 0;
        $data['foam_washes'] = $data['foam_washes'] ?? 0;
        $data['total_washes'] = $data['exterior_washes'] + $data['interior_washes'] + $data['foam_washes'];

        if ($data['total_washes'] <= 0) {
            throw ValidationException::withMessages(['total_washes' => 'At least one wash must be included.']);
        }

        $isGlobal = (bool) ($data['is_global'] ?? false);
        if ($cityScope->isCityAdmin($user) && $isGlobal) {
            throw ValidationException::withMessages(['is_global' => 'City Admin cannot create global plans.']);
        }

        if ($isGlobal) {
            $data['service_city_id'] = null;
            $data['service_zone_id'] = null;
            return;
        }

        $data['service_city_id'] = $cityScope->allowedCityId($user, $request->service_city_id);
        $data['service_zone_id'] = $request->service_zone_id;
    }

    private function ensureNoDuplicate(array $data, ?int $ignoreId = null): void
    {
        $query = SubscriptionPlan::where('slug', $data['slug'])
            ->where('status', 'active')
            ->where('service_city_id', $data['service_city_id'])
            ->where('service_zone_id', $data['service_zone_id']);

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        if (($data['status'] ?? 'active') === 'active' && $query->exists()) {
            throw ValidationException::withMessages(['slug' => 'An active plan with this slug already exists for this city/zone.']);
        }
    }
}
