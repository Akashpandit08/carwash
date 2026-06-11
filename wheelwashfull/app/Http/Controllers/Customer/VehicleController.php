<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::where('user_id', auth()->id())->get();
        return view('customer.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('customer.vehicles.create');
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

        Vehicle::create($validated);

        return redirect()->route('customer.vehicles.index')->with('success', 'Vehicle added successfully');
    }

    public function edit(Vehicle $vehicle)
    {
        if ($vehicle->user_id !== auth()->id()) {
            abort(403);
        }

        return view('customer.vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->user_id !== auth()->id()) {
            abort(403);
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

        return redirect()->route('customer.vehicles.index')->with('success', 'Vehicle updated successfully');
    }

    public function destroy(Vehicle $vehicle)
    {
        if ($vehicle->user_id !== auth()->id()) {
            abort(403);
        }

        $vehicle->delete();

        return redirect()->route('customer.vehicles.index')->with('success', 'Vehicle deleted successfully');
    }
}
