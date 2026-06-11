<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::where('user_id', auth()->id())->get();
        return response()->json([
            'success' => true,
            'data' => $vehicles
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_type' => 'required|in:car,bike,suv,truck',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'registration_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicles')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                }),
            ],
            'color' => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = auth()->id();

        $vehicle = Vehicle::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle added successfully',
            'data' => $vehicle
        ], 201);
    }

    public function show(Vehicle $vehicle)
    {
        if ($vehicle->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $vehicle
        ]);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validated = $request->validate([
            'vehicle_type' => 'required|in:car,bike,suv,truck',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'registration_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicles')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                })->ignore($vehicle->id),
            ],
            'color' => 'nullable|string|max:255',
        ]);

        $vehicle->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully',
            'data' => $vehicle
        ]);
    }

    public function destroy(Vehicle $vehicle)
    {
        if ($vehicle->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully'
        ]);
    }
}
