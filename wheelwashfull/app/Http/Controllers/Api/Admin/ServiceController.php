<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\CityScopeService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    public function index(CityScopeService $cityScope)
    {
        $query = Service::with(['category', 'serviceCity', 'serviceZone']);
        $user = auth()->user();

        if ($cityScope->isCityAdmin($user)) {
            $query->where('service_city_id', $user->service_city_id)->where('is_global', false);
        } elseif (request()->filled('service_city_id')) {
            $query->where('service_city_id', request('service_city_id'));
        }

        if (request()->filled('service_zone_id')) {
            $query->where('service_zone_id', request('service_zone_id'));
        }
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }
        if (request()->has('is_global')) {
            $query->where('is_global', filter_var(request('is_global'), FILTER_VALIDATE_BOOL));
        }

        $services = $query->orderBy('sort_order')->latest()->get();
        return response()->json(['success' => true, 'data' => $services]);
    }

    public function store(Request $request, CityScopeService $cityScope)
    {
        $validated = $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'service_city_id' => 'nullable|exists:service_cities,id',
            'service_zone_id' => 'nullable|exists:service_zones,id',
            'service_area' => 'nullable|string|max:255',
            'is_global' => 'boolean',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'vehicle_types' => 'nullable|array',
            'vehicle_types.*' => 'string',
            'wash_type' => 'nullable|string|in:door_to_door,pickup_drop,drive_in',
            'is_active' => 'boolean',
            'status' => 'nullable|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $this->applyCityRules($validated, $request, $cityScope);
        $validated['category_id'] = $validated['service_category_id'];
        unset($validated['service_category_id']);
        $validated['status'] = $validated['status'] ?? (($validated['is_active'] ?? true) ? 'active' : 'inactive');
        $validated['is_active'] = ($validated['status'] ?? 'active') === 'active';

        $service = Service::create($validated);
        return response()->json(['success' => true, 'message' => 'Service created successfully.', 'data' => $service->load(['serviceCity', 'serviceZone'])], 201);
    }

    public function show(Service $service, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $service);

        return response()->json(['success' => true, 'data' => $service->load(['category', 'serviceCity', 'serviceZone'])]);
    }

    public function update(Request $request, Service $service, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $service);

        $validated = $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'service_city_id' => 'nullable|exists:service_cities,id',
            'service_zone_id' => 'nullable|exists:service_zones,id',
            'service_area' => 'nullable|string|max:255',
            'is_global' => 'boolean',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'vehicle_types' => 'nullable|array',
            'vehicle_types.*' => 'string',
            'wash_type' => 'nullable|string|in:door_to_door,pickup_drop,drive_in',
            'is_active' => 'boolean',
            'status' => 'nullable|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $this->applyCityRules($validated, $request, $cityScope);
        $validated['category_id'] = $validated['service_category_id'];
        unset($validated['service_category_id']);
        $validated['status'] = $validated['status'] ?? (($validated['is_active'] ?? true) ? 'active' : 'inactive');
        $validated['is_active'] = ($validated['status'] ?? 'active') === 'active';

        $service->update($validated);
        return response()->json(['success' => true, 'message' => 'Service updated successfully.', 'data' => $service->fresh(['serviceCity', 'serviceZone'])]);
    }

    public function destroy(Service $service, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $service);

        $service->delete();
        return response()->json(['success' => true, 'message' => 'Service deleted successfully.']);
    }

    private function applyCityRules(array &$validated, Request $request, CityScopeService $cityScope): void
    {
        $user = auth()->user();
        $isGlobal = (bool) ($validated['is_global'] ?? false);

        if ($cityScope->isCityAdmin($user) && $isGlobal) {
            throw ValidationException::withMessages(['is_global' => 'City Admin cannot create or edit global services.']);
        }

        if ($isGlobal) {
            $validated['service_city_id'] = null;
            $validated['service_zone_id'] = null;
            return;
        }

        $validated['service_city_id'] = $cityScope->allowedCityId($user, $request->service_city_id);
        $validated['service_zone_id'] = $request->service_zone_id;
    }
}
