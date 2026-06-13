<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserDevice;
use Illuminate\Support\Facades\Log;

class AppController extends Controller
{
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'role' => 'nullable|string',
            'is_online' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        
        $profile = match ($user->role) {
            'worker' => $user->workerProfile,
            'pickup_driver' => $user->pickupDriverProfile,
            'partner' => $user->partnerProfile,
            default => null,
        };

        if ($profile) {
            $updates = [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ];
            
            if ($request->has('is_online')) {
                $updates['current_status'] = $request->boolean('is_online') ? 'online' : 'offline';
            }
            
            $profile->update($updates);
        }

        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'is_online' => 'required|boolean',
        ]);

        $user = auth()->user();
        
        $profile = match ($user->role) {
            'worker' => $user->workerProfile,
            'pickup_driver' => $user->pickupDriverProfile,
            'partner' => $user->partnerProfile,
            default => null,
        };

        if ($profile) {
            $profile->update([
                'current_status' => $request->boolean('is_online') ? 'online' : 'offline'
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function registerDeviceToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'nullable|string',
        ]);

        $user = auth()->user();
        
        UserDevice::updateOrCreate(
            ['user_id' => $user->id, 'device_token' => $request->token],
            ['platform' => $request->platform ?? 'expo', 'last_active_at' => now()]
        );

        return response()->json(['success' => true]);
    }
}
