<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Generate and send OTP to user's mobile number.
     */
    public function generateOtp(string $mobileNumber): array
    {
        // Generate 6-digit OTP
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // Set OTP expiry time (10 minutes from now)
        $expiresAt = Carbon::now()->addMinutes(10);

        // Find or create user
        $user = User::firstOrCreate(
            ['mobile_number' => $mobileNumber],
            [
                'name' => 'User ' . substr($mobileNumber, -4),
                'role' => 'customer',
            ]
        );

        // Update OTP and expiry
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => $expiresAt,
        ]);

        // Send OTP via OmneaxaWhatsAppService
        app(\App\Services\OmneaxaWhatsAppService::class)->sendEvent(
            $mobileNumber,
            'otp_login',
            ['otp' => $otp],
            [
                'event_type' => 'otp_login',
                'module' => 'auth',
                'user_id' => $user->id,
                'role' => $user->role,
            ]
        );
        
        return [
            'user' => $user,
            'otp' => config('app.env') === 'production' ? null : $otp, // Only return OTP in non-production
        ];
    }

    /**
     * Verify OTP and login user.
     */
    public function verifyOtp(string $mobileNumber, string $otp): ?User
    {
        $user = User::where('mobile_number', $mobileNumber)->first();

        if (!$user) {
            return null;
        }

        // Check if OTP matches and is not expired
        if ($user->otp !== $otp) {
            return null;
        }

        if (!$user->otp_expires_at || Carbon::now()->isAfter($user->otp_expires_at)) {
            return null;
        }

        // Clear OTP after successful verification
        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return $user;
    }

    /**
     * Create authentication token for user.
     */
    public function createToken(User $user): string
    {
        $token = Str::random(80);

        $user->forceFill([
            'api_token_hash' => hash('sha256', $token),
        ])->save();

        return $token;
    }

    /**
     * Logout user by revoking tokens.
     */
    public function logout(User $user): void
    {
        $user->forceFill([
            'api_token_hash' => null,
        ])->save();
    }
}
