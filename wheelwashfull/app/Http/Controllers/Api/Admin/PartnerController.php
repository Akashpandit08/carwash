<?php

namespace App\Http\Controllers\Api\Admin;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreatePartnerRequest;
use App\Http\Resources\Api\PartnerResource;
use App\Models\PartnerProfile;
use App\Models\User;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    public function index()
    {
        return PartnerResource::collection(PartnerProfile::with('user')->latest()->paginate(request('per_page', 15)))
            ->additional(['success' => true]);
    }

    public function store(CreatePartnerRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email ?: Str::uuid().'@wheelwash.local',
            'mobile_number' => $request->mobile_number,
            'password' => 'password',
            'role' => UserRole::PARTNER,
        ]);

        $profile = PartnerProfile::create($request->safe()->except(['name', 'email', 'mobile_number']) + ['user_id' => $user->id]);

        return response()->json(['success' => true, 'data' => new PartnerResource($profile->load('user'))], 201);
    }

    public function update(CreatePartnerRequest $request, PartnerProfile $partner)
    {
        $partner->user->update($request->safe()->only(['name', 'email', 'mobile_number']));
        $partner->update($request->safe()->except(['name', 'email', 'mobile_number']));

        return response()->json(['success' => true, 'data' => new PartnerResource($partner->load('user'))]);
    }
}
