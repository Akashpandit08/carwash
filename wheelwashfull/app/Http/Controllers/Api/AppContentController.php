<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Banner;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Service;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppContentController extends Controller
{
    public function banners(Request $request)
    {
        $role = $request->user()->role;

        return response()->json([
            'success' => true,
            'data' => Banner::visibleForRole($role)->orderBy('sort_order')->orderByDesc('id')->get(),
        ]);
    }

    public function home(Request $request)
    {
        $user = $request->user();

        $services = Service::where('is_active', true)->orderBy('id')->get();
        $coupons = Coupon::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->latest()
            ->take(10)
            ->get();

        $activeBookings = Booking::with(['service', 'vehicle'])
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'banners' => Banner::visibleForRole($user->role)->orderBy('sort_order')->get(),
                'services' => $services,
                'coupons' => $coupons,
                'active_bookings' => $activeBookings,
                'recommended_services' => $services->take(5)->values(),
            ],
        ]);
    }

    public function storeDeviceToken(Request $request)
    {
        $data = $request->validate([
            'expo_push_token' => ['nullable', 'string', 'max:255'],
            'fcm_token' => ['nullable', 'string', 'max:512'],
            'device_type' => ['nullable', 'string', 'max:40'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = $request->user();
        $role = $user->role === 'pickup_driver' ? 'driver' : $user->role;

        $device = UserDevice::updateOrCreate(
            [
                'user_id' => $user->id,
                'expo_push_token' => $data['expo_push_token'] ?? null,
                'fcm_token' => $data['fcm_token'] ?? null,
            ],
            array_merge($data, [
                'role' => $role,
                'is_active' => true,
                'last_used_at' => now(),
            ])
        );

        return response()->json(['success' => true, 'data' => $device]);
    }

    public function notifications(Request $request)
    {
        $notifications = AppNotification::withoutGlobalScopes()
            ->where('channel', 'push')
            ->whereHas('recipients', fn ($query) => $query->where('user_id', Auth::id()))
            ->with(['recipients' => fn ($query) => $query->where('user_id', Auth::id())])
            ->latest()
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $notifications]);
    }
}
