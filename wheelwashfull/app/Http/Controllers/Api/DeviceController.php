<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Constants\UserRole;
use App\Models\UserDevice;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Store or update a device token for push notifications.
     *
     * POST /api/app/device-token
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'role' => ['nullable', 'string', 'in:customer,partner,worker,pickup_driver,admin,city_admin,super_admin'],
            'device_token' => ['nullable', 'string', 'max:512'],
            'expo_push_token' => ['nullable', 'string', 'max:512'],
            'device_type' => ['nullable', 'string', 'max:40'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'platform' => ['nullable', 'string', 'max:40'],
        ]);

        $user = $request->user();
        $userId = (int) ($data['user_id'] ?? $user->id);
        $role = $data['role'] ?? $user->role;
        $token = $data['device_token'] ?? $data['expo_push_token'] ?? null;

        abort_unless($token, 422, 'Device token is required.');
        abort_unless($userId === (int) $user->id || $user->role === UserRole::SUPER_ADMIN, 403, 'Cannot register a device token for another user.');
        abort_unless($role === $user->role || ($role === UserRole::ADMIN && UserRole::isAdminRole($user->role)) || $user->role === UserRole::SUPER_ADMIN, 422, 'Device role does not match authenticated user.');

        $device = UserDevice::updateOrCreate(
            [
                'user_id' => $userId,
                'device_token' => $token,
            ],
            [
                'role' => $role,
                'expo_push_token' => $token,
                'platform' => $data['platform'] ?? $data['device_type'] ?? null,
                'device_type' => $data['device_type'] ?? $data['platform'] ?? null,
                'device_name' => $data['device_name'] ?? null,
                'is_active' => true,
                'last_used_at' => now('Asia/Kolkata'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device token registered successfully.',
            'data' => $device,
        ]);
    }
}
