<?php

namespace App\Http\Controllers\Api\Admin;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreatePartnerRequest;
use App\Http\Resources\Api\PartnerResource;
use App\Models\PartnerProfile;
use App\Models\User;
use App\Services\CityScopeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    public function index(CityScopeService $cityScope)
    {
        $query = PartnerProfile::with('user.serviceCity');
        $cityScope->applyViaUser($query, auth()->user());

        return PartnerResource::collection($query->latest()->paginate(request('per_page', 15)))
            ->additional(['success' => true]);
    }

    public function store(CreatePartnerRequest $request, CityScopeService $cityScope)
    {
        $cityId = $cityScope->allowedCityId(auth()->user(), $request->service_city_id);

        $profile = DB::transaction(function () use ($request, $cityId) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email ?: Str::uuid().'@wheelwash.local',
                'mobile_number' => $request->mobile_number,
                'password' => $request->password ?: '12345678',
                'role' => UserRole::PARTNER,
                'service_city_id' => $cityId,
                'service_zone_id' => $request->service_zone_id,
            ]);

            $profileData = $request->safe()->except(['name', 'email', 'mobile_number', 'password', 'location_lat', 'location_lng']);
            if ($request->has('location_lat')) $profileData['latitude'] = $request->location_lat;
            if ($request->has('location_lng')) $profileData['longitude'] = $request->location_lng;

            return PartnerProfile::create($profileData + ['user_id' => $user->id]);
        });

        return response()->json(['success' => true, 'data' => new PartnerResource($profile->load('user'))], 201);
    }

    public function update(\App\Http\Requests\Api\UpdatePartnerRequest $request, PartnerProfile $partner, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $partner->user);
        $cityId = $cityScope->allowedCityId(auth()->user(), $request->service_city_id);

        DB::transaction(function () use ($request, $partner, $cityId) {
            $userData = $request->safe()->only(['name', 'email', 'mobile_number']);
            $userData['service_city_id'] = $cityId;
            $userData['service_zone_id'] = $request->service_zone_id;
            if ($request->filled('password')) {
                $userData['password'] = $request->password;
            }
            $partner->user->update($userData);

            $profileData = $request->safe()->except(['name', 'email', 'mobile_number', 'password', 'location_lat', 'location_lng']);
            if ($request->has('location_lat')) $profileData['latitude'] = $request->location_lat;
            if ($request->has('location_lng')) $profileData['longitude'] = $request->location_lng;

            $partner->update($profileData);
        });

        return response()->json(['success' => true, 'data' => new PartnerResource($partner->load('user'))]);
    }

    public function show(PartnerProfile $partner, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $partner->user);

        return response()->json(['success' => true, 'data' => new PartnerResource($partner->load('user'))]);
    }

    public function toggleStatus(PartnerProfile $partner, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $partner->user);

        $partner->current_status = $partner->current_status === 'active' ? 'inactive' : 'active';
        $partner->save();

        return response()->json(['success' => true, 'data' => new PartnerResource($partner->load('user'))]);
    }
}
