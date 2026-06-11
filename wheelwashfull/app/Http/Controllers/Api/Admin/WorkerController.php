<?php

namespace App\Http\Controllers\Api\Admin;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateWorkerRequest;
use App\Http\Resources\Api\WorkerResource;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Support\Str;

class WorkerController extends Controller
{
    public function index()
    {
        return WorkerResource::collection(WorkerProfile::with('user')->latest()->paginate(request('per_page', 15)))
            ->additional(['success' => true]);
    }

    public function store(CreateWorkerRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email ?: Str::uuid().'@wheelwash.local',
            'mobile_number' => $request->mobile_number,
            'password' => 'password',
            'role' => UserRole::WORKER,
        ]);

        $profile = WorkerProfile::create($request->safe()->except(['name', 'email', 'mobile_number']) + ['user_id' => $user->id]);

        return response()->json(['success' => true, 'data' => new WorkerResource($profile->load('user'))], 201);
    }

    public function update(CreateWorkerRequest $request, WorkerProfile $worker)
    {
        $worker->user->update($request->safe()->only(['name', 'email', 'mobile_number']));
        $worker->update($request->safe()->except(['name', 'email', 'mobile_number']));

        return response()->json(['success' => true, 'data' => new WorkerResource($worker->load('user'))]);
    }
}
