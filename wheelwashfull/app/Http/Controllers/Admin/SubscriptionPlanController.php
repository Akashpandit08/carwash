<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCity;
use App\Models\ServiceZone;
use App\Models\SubscriptionPlan;
use App\Services\CityScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request, CityScopeService $cityScope)
    {
        $query = SubscriptionPlan::with(['serviceCity', 'serviceZone']);
        $this->applyScope($query, $request, $cityScope);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('service_zone_id')) {
            $query->where('service_zone_id', $request->service_zone_id);
        }

        return view('admin.subscription-plans.index', [
            'plans' => $query->orderBy('sort_order')->latest()->paginate(20),
            'cities' => $this->citiesForAdmin($cityScope),
            'zones' => ServiceZone::with('city')->orderBy('name')->get(),
        ]);
    }

    public function create(CityScopeService $cityScope)
    {
        return view('admin.subscription-plans.form', [
            'plan' => new SubscriptionPlan(['status' => 'active', 'duration_days' => 30, 'doorstep_included' => true]),
            'cities' => $this->citiesForAdmin($cityScope),
            'zones' => ServiceZone::with('city')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, CityScopeService $cityScope)
    {
        $data = $this->validated($request, $cityScope);
        SubscriptionPlan::create($data);

        return redirect()->route('admin.subscription-plans.index', $this->cityQuery())->with('success', 'Subscription plan created.');
    }

    public function edit(SubscriptionPlan $subscriptionPlan, CityScopeService $cityScope)
    {
        $this->ensurePlanAccess($subscriptionPlan, $cityScope);

        return view('admin.subscription-plans.form', [
            'plan' => $subscriptionPlan,
            'cities' => $this->citiesForAdmin($cityScope),
            'zones' => ServiceZone::with('city')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan, CityScopeService $cityScope)
    {
        $this->ensurePlanAccess($subscriptionPlan, $cityScope);
        $subscriptionPlan->update($this->validated($request, $cityScope, $subscriptionPlan));

        return redirect()->route('admin.subscription-plans.index', $this->cityQuery())->with('success', 'Subscription plan updated.');
    }

    public function toggle(SubscriptionPlan $subscriptionPlan, CityScopeService $cityScope)
    {
        $this->ensurePlanAccess($subscriptionPlan, $cityScope);
        $subscriptionPlan->update(['status' => $subscriptionPlan->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', 'Plan status updated.');
    }

    private function validated(Request $request, CityScopeService $cityScope, ?SubscriptionPlan $plan = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'service_city_id' => ['nullable', 'exists:service_cities,id'],
            'service_zone_id' => ['nullable', 'exists:service_zones,id'],
            'service_area' => ['nullable', 'string', 'max:255'],
            'is_global' => ['nullable', 'boolean'],
            'exterior_washes' => ['nullable', 'integer', 'min:0'],
            'interior_washes' => ['nullable', 'integer', 'min:0'],
            'foam_washes' => ['nullable', 'integer', 'min:0'],
            'tyre_polish_included' => ['nullable', 'boolean'],
            'dashboard_wipe_included' => ['nullable', 'boolean'],
            'vacuum_included' => ['nullable', 'boolean'],
            'priority_booking' => ['nullable', 'boolean'],
            'pickup_drop_included' => ['nullable', 'boolean'],
            'doorstep_included' => ['nullable', 'boolean'],
            'max_washes_per_week' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'terms' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_global'] = $request->boolean('is_global');
        if ($cityScope->isCityAdmin(auth()->user()) && $data['is_global']) {
            throw ValidationException::withMessages(['is_global' => 'City Admin cannot create global plans.']);
        }
        if ($data['is_global']) {
            $data['service_city_id'] = null;
            $data['service_zone_id'] = null;
        } else {
            $data['service_city_id'] = $cityScope->allowedCityId(auth()->user(), $data['service_city_id'] ?? null);
        }

        foreach (['exterior_washes', 'interior_washes', 'foam_washes', 'sort_order'] as $field) {
            $data[$field] = $data[$field] ?? 0;
        }
        $data['total_washes'] = $data['exterior_washes'] + $data['interior_washes'] + $data['foam_washes'];
        foreach (['tyre_polish_included', 'dashboard_wipe_included', 'vacuum_included', 'priority_booking', 'pickup_drop_included', 'doorstep_included'] as $field) {
            $data[$field] = $request->boolean($field);
        }
        $data['status'] = $data['status'] ?? 'active';

        $duplicate = SubscriptionPlan::where('slug', $data['slug'])
            ->where('service_city_id', $data['service_city_id'])
            ->where('service_zone_id', $data['service_zone_id'])
            ->when($plan, fn ($query) => $query->whereKeyNot($plan->id))
            ->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['slug' => 'A plan with this slug already exists for this city/zone.']);
        }

        return $data;
    }

    private function applyScope($query, Request $request, CityScopeService $cityScope): void
    {
        if ($cityScope->isCityAdmin(auth()->user())) {
            $query->where('service_city_id', auth()->user()->service_city_id)->where('is_global', false);
        } elseif ($request->filled('service_city_id')) {
            $query->where('service_city_id', $request->service_city_id);
        }
    }

    private function ensurePlanAccess(SubscriptionPlan $plan, CityScopeService $cityScope): void
    {
        if (!$plan->is_global) {
            $cityScope->ensureCanAccessModel(auth()->user(), $plan);
        }
    }

    private function citiesForAdmin(CityScopeService $cityScope)
    {
        return $cityScope->isSuperAdmin(auth()->user())
            ? ServiceCity::orderBy('sort_order')->orderBy('name')->get()
            : ServiceCity::whereKey(auth()->user()->service_city_id)->get();
    }

    private function cityQuery(): array
    {
        return request('service_city_id') ? ['service_city_id' => request('service_city_id')] : [];
    }
}
