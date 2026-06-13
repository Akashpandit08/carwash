<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PickupDriverProfile;
use App\Constants\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = User::where('role', UserRole::PICKUP_DRIVER)
            ->whereHas('pickupDriverProfile', function ($query) {
                $query->where('partner_id', auth()->id());
            })
            ->with(['pickupDriverProfile'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $drivers->map(function ($d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'mobile_number' => $d->mobile_number,
                    'status' => $d->status,
                    'current_status' => $d->pickupDriverProfile->current_status ?? 'offline',
                    'vehicle_type' => $d->pickupDriverProfile->vehicle_type ?? null,
                    'license_number' => $d->pickupDriverProfile->license_number ?? null,
                    'latitude' => $d->pickupDriverProfile->latitude ?? null,
                    'longitude' => $d->pickupDriverProfile->longitude ?? null,
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile_number' => 'required|string|unique:users,mobile_number',
            'password' => 'required|string|min:6',
            'vehicle_type' => 'nullable|string',
            'license_number' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'mobile_number' => $validated['mobile_number'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::PICKUP_DRIVER,
            'status' => $validated['status'] ?? 'active',
        ]);

        PickupDriverProfile::create([
            'user_id' => $user->id,
            'partner_id' => auth()->id(),
            'vehicle_type' => $validated['vehicle_type'] ?? null,
            'license_number' => $validated['license_number'] ?? null,
            'current_status' => 'offline',
        ]);

        return response()->json(['success' => true, 'message' => 'Driver added successfully.', 'data' => $user], 201);
    }

    public function show($id)
    {
        $driver = $this->getDriver($id);
        return response()->json(['success' => true, 'data' => $driver->load('pickupDriverProfile')]);
    }

    public function update(Request $request, $id)
    {
        $driver = $this->getDriver($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'mobile_number' => ['sometimes', 'required', 'string', Rule::unique('users')->ignore($driver->id)],
            'password' => 'nullable|string|min:6',
            'vehicle_type' => 'nullable|string',
            'license_number' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $driver->update($validated);
        
        $profileUpdates = [];
        if (array_key_exists('vehicle_type', $validated)) $profileUpdates['vehicle_type'] = $validated['vehicle_type'];
        if (array_key_exists('license_number', $validated)) $profileUpdates['license_number'] = $validated['license_number'];
        
        if (!empty($profileUpdates)) {
            $driver->pickupDriverProfile()->update($profileUpdates);
        }

        return response()->json(['success' => true, 'message' => 'Driver updated successfully.']);
    }

    public function destroy($id)
    {
        $driver = $this->getDriver($id);
        $driver->delete();
        return response()->json(['success' => true, 'message' => 'Driver deleted successfully.']);
    }

    public function location($id)
    {
        $driver = $this->getDriver($id);
        return response()->json([
            'success' => true,
            'data' => [
                'latitude' => $driver->pickupDriverProfile->latitude ?? null,
                'longitude' => $driver->pickupDriverProfile->longitude ?? null,
                'last_updated' => $driver->pickupDriverProfile->updated_at ?? null,
            ]
        ]);
    }

    private function getDriver($id)
    {
        return User::where('role', UserRole::PICKUP_DRIVER)
            ->whereHas('pickupDriverProfile', function ($query) {
                $query->where('partner_id', auth()->id());
            })->findOrFail($id);
    }
}
