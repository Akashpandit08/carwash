<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
                    'user' => $this->userPayload($user),
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
                'user' => $this->userPayload($request->user()),
            ],
        ], 200);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile_number' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginIdentifier = trim($validated['mobile_number']);

        $user = User::where('mobile_number', $loginIdentifier)
                    ->orWhere('email', $loginIdentifier)
                    ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (($user->status ?? 'active') !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive.',
            ], 403);
        }

        $token = $this->authService->createToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => $this->userPayload($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
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

    private function userPayload(User $user): array
    {
        $user->loadMissing(['serviceCity', 'serviceZone']);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'mobile_number' => $user->mobile_number,
            'role' => $user->role,
            'status' => $user->status,
            'email' => $user->email,
            'service_city_id' => $user->service_city_id,
            'service_city_name' => $user->serviceCity?->name,
            'service_zone_id' => $user->service_zone_id,
            'service_zone_name' => $user->serviceZone?->name,
            'permissions' => $this->permissionsFor($user),
        ];
    }

    private function permissionsFor(User $user): array
    {
        if ($user->isSuperAdmin()) {
            return [
                'manage_all_cities',
                'manage_locations',
                'manage_city_admins',
                'view_all_city_data',
                'assign_team',
            ];
        }

        if ($user->isCityAdmin()) {
            return [
                'view_own_city_data',
                'manage_own_city_operations',
                'assign_own_city_team',
            ];
        }

        return [];
    }
}
