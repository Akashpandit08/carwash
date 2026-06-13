<?php

namespace App\Http\Controllers\Api\Admin;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CityAdminController extends Controller
{
    public function index()
    {
        return UserResource::collection(
            User::with(['serviceCity', 'serviceZone'])
                ->where('role', UserRole::CITY_ADMIN)
                ->latest()
                ->paginate(request('per_page', 15))
        )->additional(['success' => true]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $admin = User::create([
            'name' => $data['name'],
            'mobile_number' => $data['mobile_number'],
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password'] ?? '12345678'),
            'role' => UserRole::CITY_ADMIN,
            'status' => $data['status'] ?? 'active',
            'service_city_id' => $data['service_city_id'],
            'service_zone_id' => $data['service_zone_id'] ?? null,
        ]);

        return response()->json(['success' => true, 'data' => new UserResource($admin->load(['serviceCity', 'serviceZone']))], 201);
    }

    public function show(User $id)
    {
        abort_unless($id->role === UserRole::CITY_ADMIN, 404);

        return response()->json(['success' => true, 'data' => new UserResource($id->load(['serviceCity', 'serviceZone']))]);
    }

    public function update(Request $request, User $id)
    {
        abort_unless($id->role === UserRole::CITY_ADMIN, 404);

        $data = $this->validated($request, $id);
        $update = [
            'name' => $data['name'],
            'mobile_number' => $data['mobile_number'],
            'email' => $data['email'] ?? null,
            'status' => $data['status'] ?? 'active',
            'service_city_id' => $data['service_city_id'],
            'service_zone_id' => $data['service_zone_id'] ?? null,
        ];

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $id->update($update);

        return response()->json(['success' => true, 'data' => new UserResource($id->fresh(['serviceCity', 'serviceZone']))]);
    }

    public function destroy(User $id)
    {
        abort_unless($id->role === UserRole::CITY_ADMIN, 404);
        $id->delete();

        return response()->json(['success' => true, 'message' => 'City admin deleted.']);
    }

    private function validated(Request $request, ?User $admin = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:20', Rule::unique('users', 'mobile_number')->ignore($admin?->id)],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($admin?->id)],
            'password' => [$admin ? 'nullable' : 'nullable', 'string', 'min:6'],
            'service_city_id' => ['required', 'exists:service_cities,id'],
            'service_zone_id' => ['nullable', 'exists:service_zones,id'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
    }
}
