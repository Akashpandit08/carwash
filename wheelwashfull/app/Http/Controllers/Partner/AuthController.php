<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'partner') {
            return redirect()->route('partner.jobs.today');
        }

        return view('partner.auth.login');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|regex:/^[0-9]{10,15}$/',
        ]);

        $user = User::where('mobile_number', $request->mobile_number)->first();

        if (!$user || $user->role !== 'partner') {
            return response()->json([
                'success' => false,
                'message' => 'This mobile number is not registered as a partner.',
            ], 403);
        }

        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        Session::put('partner_otp_mobile', $request->mobile_number);

        app(\App\Services\NotificationService::class)->sendOtp($user, $otp);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
            'otp' => config('app.env') !== 'production' ? $otp : null,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|regex:/^[0-9]{10,15}$/',
            'otp' => 'required|size:6',
        ]);

        $user = User::where('mobile_number', $request->mobile_number)->first();

        if (!$user || $user->role !== 'partner') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized partner account.',
            ], 403);
        }

        if ($user->otp !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
            ], 401);
        }

        if (Carbon::now()->isAfter($user->otp_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired',
            ], 401);
        }

        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        Auth::login($user);
        Session::forget('partner_otp_mobile');

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => route('partner.jobs.today'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('partner.login');
    }
}
