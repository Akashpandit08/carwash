<?php

namespace App\Http\Controllers\Api\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateLocationRequest;
use App\Http\Resources\Api\TrackingResource;
use App\Services\LocationTrackingService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function update(UpdateLocationRequest $request, LocationTrackingService $service)
    {
        $location = $service->update(auth()->user(), $request->validated());

        return response()->json(['success' => true, 'data' => new TrackingResource($location->load('user'))], 201);
    }

    public function onlineStatus(Request $request)
    {
        $data = $request->validate([
            'role' => ['nullable', 'string'],
            'is_online' => ['required', 'boolean'],
        ]);

        $status = $data['is_online'] ? 'available' : 'offline';
        $user = auth()->user();

        if ($user->role === 'worker') {
            $user->workerProfile()->updateOrCreate(['user_id' => $user->id], ['current_status' => $status]);
        }

        if ($user->role === 'pickup_driver') {
            $user->pickupDriverProfile()->updateOrCreate(['user_id' => $user->id], ['current_status' => $status]);
        }

        return response()->json(['success' => true, 'data' => ['is_online' => (bool) $data['is_online'], 'status' => $status]]);
    }
}
