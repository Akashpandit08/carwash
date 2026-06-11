<?php

namespace App\Http\Controllers\Customer;

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
        if (Auth::check()) {
            return redirect()->route('customer.home');
        }
        return view('customer.auth.login');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|regex:/^[0-9]{10,15}$/',
        ]);

        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(10);

        $user = User::firstOrCreate(
            ['mobile_number' => $request->mobile_number],
            [
                'name' => 'User ' . substr($request->mobile_number, -4),
                'role' => 'customer',
            ]
        );

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => $expiresAt,
        ]);

        Session::put('otp_mobile', $request->mobile_number);
        Session::put('dev_otp', $otp);

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

        if (!$user || $user->otp !== $request->otp) {
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
        Session::forget(['otp_mobile', 'dev_otp']);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => route('customer.home'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('customer.login');
    }
}
