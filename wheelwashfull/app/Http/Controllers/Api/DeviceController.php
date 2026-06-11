<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', 'string', 'in:customer,partner,worker,pickup_driver,admin'],
            'device_token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'max:40'],
        ]);

        $device = UserDevice::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'device_token' => $data['device_token'],
            ],
            [
                'role' => $data['role'],
                'expo_push_token' => $data['device_token'],
                'platform' => $data['platform'] ?? null,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device token registered successfully.',
            'data' => $device,
        ]);
    }
}
