<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Send OTP to user's mobile number.
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->generateOtp($request->mobile_number);

            $response = [
                'success' => true,
                'message' => 'OTP sent successfully.',
                'data' => [
                    'mobile_number' => $request->mobile_number,
                    'expires_in' => '10 minutes',
                ],
            ];

            // Include OTP in response if OTP_DEBUG is true
            if (config('app.otp_debug')) {
                $response['otp'] = $result['otp'];
                $response['data']['otp'] = $result['otp'];
                $response['data']['dev_note'] = 'OTP is exposed because OTP_DEBUG is enabled.';
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Verify OTP and login user.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->verifyOtp(
                $request->mobile_number,
                $request->otp
            );

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP.',
                ], 401);
            }

            // Create authentication token
            $token = $this->authService->createToken($user);

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'mobile_number' => $user->mobile_number,
                        'role' => $user->role,
                        'email' => $user->email,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get authenticated user details.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'mobile_number' => $request->user()->mobile_number,
                    'role' => $request->user()->role,
                    'email' => $request->user()->email,
                ],
            ],
        ], 200);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Logout successful.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
