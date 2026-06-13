<?php

namespace App\Http\Controllers\Api\Admin;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreatePickupDriverRequest;
use App\Http\Resources\Api\PickupDriverResource;
use App\Models\PickupDriverProfile;
use App\Models\User;
use App\Services\CityScopeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PickupDriverController extends Controller
{
    public function index(CityScopeService $cityScope)
    {
        $query = PickupDriverProfile::with('user.serviceCity');
        $cityScope->applyViaUser($query, auth()->user());

        return PickupDriverResource::collection($query->latest()->paginate(request('per_page', 15)))
            ->additional(['success' => true]);
    }

    public function store(CreatePickupDriverRequest $request, CityScopeService $cityScope)
    {
        $cityId = $cityScope->allowedCityId(auth()->user(), $request->service_city_id);

        $profile = DB::transaction(function () use ($request, $cityId) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email ?: Str::uuid().'@wheelwash.local',
                'mobile_number' => $request->mobile_number,
                'password' => $request->password ?: '12345678',
                'role' => UserRole::PICKUP_DRIVER,
                'service_city_id' => $cityId,
                'service_zone_id' => $request->service_zone_id,
            ]);

            $profileData = $request->safe()->except(['name', 'email', 'mobile_number', 'password', 'location_lat', 'location_lng']);
            if ($request->has('location_lat')) $profileData['latitude'] = $request->location_lat;
            if ($request->has('location_lng')) $profileData['longitude'] = $request->location_lng;

            return PickupDriverProfile::create($profileData + ['user_id' => $user->id]);
        });

        return response()->json(['success' => true, 'data' => new PickupDriverResource($profile->load('user'))], 201);
    }

    public function update(\App\Http\Requests\Api\UpdatePickupDriverRequest $request, PickupDriverProfile $driver, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $driver->user);
        $cityId = $cityScope->allowedCityId(auth()->user(), $request->service_city_id);

        DB::transaction(function () use ($request, $driver, $cityId) {
            $userData = $request->safe()->only(['name', 'email', 'mobile_number']);
            $userData['service_city_id'] = $cityId;
            $userData['service_zone_id'] = $request->service_zone_id;
            if ($request->filled('password')) {
                $userData['password'] = $request->password;
            }
            $driver->user->update($userData);

            $profileData = $request->safe()->except(['name', 'email', 'mobile_number', 'password', 'location_lat', 'location_lng']);
            if ($request->has('location_lat')) $profileData['latitude'] = $request->location_lat;
            if ($request->has('location_lng')) $profileData['longitude'] = $request->location_lng;

            $driver->update($profileData);
        });

        return response()->json(['success' => true, 'data' => new PickupDriverResource($driver->load('user'))]);
    }

    public function show(PickupDriverProfile $driver, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $driver->user);

        return response()->json(['success' => true, 'data' => new PickupDriverResource($driver->load('user', 'partner.partnerProfile'))]);
    }

    public function toggleStatus(PickupDriverProfile $driver, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $driver->user);

        $driver->current_status = $driver->current_status === 'active' ? 'inactive' : 'active';
        $driver->save();

        return response()->json(['success' => true, 'data' => new PickupDriverResource($driver->load('user'))]);
    }
}
