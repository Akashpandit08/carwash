<?php

namespace App\Http\Controllers\Api\Admin;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateWorkerRequest;
use App\Http\Resources\Api\WorkerResource;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Services\CityScopeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkerController extends Controller
{
    public function index(CityScopeService $cityScope)
    {
        $query = WorkerProfile::with('user.serviceCity');
        $cityScope->applyViaUser($query, auth()->user());

        return WorkerResource::collection($query->latest()->paginate(request('per_page', 15)))
            ->additional(['success' => true]);
    }

    public function store(CreateWorkerRequest $request, CityScopeService $cityScope)
    {
        $cityId = $cityScope->allowedCityId(auth()->user(), $request->service_city_id);

        $profile = DB::transaction(function () use ($request, $cityId) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email ?: Str::uuid().'@wheelwash.local',
                'mobile_number' => $request->mobile_number,
                'password' => $request->password ?: '12345678',
                'role' => UserRole::WORKER,
                'service_city_id' => $cityId,
                'service_zone_id' => $request->service_zone_id,
            ]);

            $profileData = $request->safe()->except(['name', 'email', 'mobile_number', 'password', 'location_lat', 'location_lng']);
            if ($request->has('location_lat')) $profileData['latitude'] = $request->location_lat;
            if ($request->has('location_lng')) $profileData['longitude'] = $request->location_lng;

            return WorkerProfile::create($profileData + ['user_id' => $user->id]);
        });

        return response()->json(['success' => true, 'data' => new WorkerResource($profile->load('user'))], 201);
    }

    public function update(\App\Http\Requests\Api\UpdateWorkerRequest $request, WorkerProfile $worker, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $worker->user);
        $cityId = $cityScope->allowedCityId(auth()->user(), $request->service_city_id);

        DB::transaction(function () use ($request, $worker, $cityId) {
            $userData = $request->safe()->only(['name', 'email', 'mobile_number']);
            $userData['service_city_id'] = $cityId;
            $userData['service_zone_id'] = $request->service_zone_id;
            if ($request->filled('password')) {
                $userData['password'] = $request->password;
            }
            $worker->user->update($userData);

            $profileData = $request->safe()->except(['name', 'email', 'mobile_number', 'password', 'location_lat', 'location_lng']);
            if ($request->has('location_lat')) $profileData['latitude'] = $request->location_lat;
            if ($request->has('location_lng')) $profileData['longitude'] = $request->location_lng;
            
            $worker->update($profileData);
        });

        return response()->json(['success' => true, 'data' => new WorkerResource($worker->load('user'))]);
    }

    public function show(WorkerProfile $worker, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $worker->user);

        return response()->json(['success' => true, 'data' => new WorkerResource($worker->load('user', 'partner.partnerProfile'))]);
    }

    public function toggleStatus(WorkerProfile $worker, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $worker->user);

        $worker->current_status = $worker->current_status === 'active' ? 'inactive' : 'active';
        $worker->save();

        return response()->json(['success' => true, 'data' => new WorkerResource($worker->load('user'))]);
    }
}
