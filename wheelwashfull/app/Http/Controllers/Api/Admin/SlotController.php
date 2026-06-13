<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slot;
use App\Services\CityScopeService;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    public function index(CityScopeService $cityScope)
    {
        $query = Slot::query();
        $cityScope->apply($query, auth()->user());

        $slots = $query->latest('date')->get();
        return response()->json(['success' => true, 'data' => $slots]);
    }

    public function store(Request $request, CityScopeService $cityScope)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'service_city_id' => 'nullable|exists:service_cities,id',
            'service_zone_id' => 'nullable|exists:service_zones,id',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'wash_type' => 'nullable|string|in:door_to_door,pickup_drop,drive_in',
            'max_bookings' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['service_city_id'] = $cityScope->allowedCityId(auth()->user(), $request->service_city_id);
        $validated['service_zone_id'] = $request->service_zone_id;

        $slot = Slot::create($validated);
        return response()->json(['success' => true, 'message' => 'Slot created.', 'data' => $slot], 201);
    }

    public function show(Slot $slot, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $slot);

        return response()->json(['success' => true, 'data' => $slot]);
    }

    public function update(Request $request, Slot $slot, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $slot);

        $validated = $request->validate([
            'date' => 'required|date',
            'service_city_id' => 'nullable|exists:service_cities,id',
            'service_zone_id' => 'nullable|exists:service_zones,id',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'wash_type' => 'nullable|string|in:door_to_door,pickup_drop,drive_in',
            'max_bookings' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['service_city_id'] = $cityScope->allowedCityId(auth()->user(), $request->service_city_id);
        $validated['service_zone_id'] = $request->service_zone_id;

        $slot->update($validated);
        return response()->json(['success' => true, 'message' => 'Slot updated.', 'data' => $slot]);
    }

    public function destroy(Slot $slot, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $slot);

        $slot->delete();
        return response()->json(['success' => true, 'message' => 'Slot deleted.']);
    }
}
