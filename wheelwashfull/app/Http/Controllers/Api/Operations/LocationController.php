<?php

namespace App\Http\Controllers\Api\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateLocationRequest;
use App\Http\Resources\Api\TrackingResource;
use App\Services\LocationTrackingService;

class LocationController extends Controller
{
    public function update(UpdateLocationRequest $request, LocationTrackingService $service)
    {
        $location = $service->update(auth()->user(), $request->validated());

        return response()->json(['success' => true, 'data' => new TrackingResource($location->load('user'))], 201);
    }
}
