<?php

namespace App\Http\Controllers\Api\Admin;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreatePickupDriverRequest;
use App\Http\Resources\Api\PickupDriverResource;
use App\Models\PickupDriverProfile;
use App\Models\User;
use Illuminate\Support\Str;

class PickupDriverController extends Controller
{
    public function index()
    {
        return PickupDriverResource::collection(PickupDriverProfile::with('user')->latest()->paginate(request('per_page', 15)))
            ->additional(['success' => true]);
    }

    public function store(CreatePickupDriverRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email ?: Str::uuid().'@wheelwash.local',
            'mobile_number' => $request->mobile_number,
            'password' => 'password',
            'role' => UserRole::PICKUP_DRIVER,
        ]);

        $profile = PickupDriverProfile::create($request->safe()->except(['name', 'email', 'mobile_number']) + ['user_id' => $user->id]);

        return response()->json(['success' => true, 'data' => new PickupDriverResource($profile->load('user'))], 201);
    }

    public function update(CreatePickupDriverRequest $request, PickupDriverProfile $driver)
    {
        $driver->user->update($request->safe()->only(['name', 'email', 'mobile_number']));
        $driver->update($request->safe()->except(['name', 'email', 'mobile_number']));

        return response()->json(['success' => true, 'data' => new PickupDriverResource($driver->load('user'))]);
    }
}
