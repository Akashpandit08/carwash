<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    /**
     * Display a listing of coupons
     */
    public function index()
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $coupons,
        ]);
    }

    /**
     * Store a newly created coupon
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:coupons,code|max:50',
            'description' => 'nullable|string|max:500',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Additional validation for percentage discount
        if ($request->discount_type === 'percentage' && $request->discount_value > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Percentage discount cannot exceed 100%',
            ], 422);
        }

        // Convert code to uppercase for consistency
        $data = $validator->validated();
        $data['code'] = strtoupper($data['code']);
        $data['used_count'] = 0;

        $coupon = Coupon::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Coupon created successfully',
            'data' => $coupon,
        ], 201);
    }

    /**
     * Display the specified coupon
     */
    public function show(Coupon $coupon)
    {
        return response()->json([
            'success' => true,
            'data' => $coupon,
        ]);
    }

    /**
     * Update the specified coupon
     */
    public function update(Request $request, Coupon $coupon)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'description' => 'nullable|string|max:500',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Additional validation for percentage discount
        if ($request->discount_type === 'percentage' && $request->discount_value > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Percentage discount cannot exceed 100%',
            ], 422);
        }

        // Validate usage limit if it's being reduced
        if ($request->has('usage_limit') && $request->usage_limit < $coupon->used_count) {
            return response()->json([
                'success' => false,
                'message' => "Usage limit cannot be less than current usage count ({$coupon->used_count})",
            ], 422);
        }

        $data = $validator->validated();
        $data['code'] = strtoupper($data['code']);

        $coupon->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Coupon updated successfully',
            'data' => $coupon,
        ]);
    }

    /**
     * Remove the specified coupon
     */
    public function destroy(Coupon $coupon)
    {
        // Check if coupon is being used
        if ($coupon->used_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete coupon that has been used. Consider disabling it instead.',
            ], 422);
        }

        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coupon deleted successfully',
        ]);
    }

    /**
     * Toggle coupon active status
     */
    public function toggleStatus(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);

        return response()->json([
            'success' => true,
            'message' => $coupon->is_active ? 'Coupon enabled successfully' : 'Coupon disabled successfully',
            'data' => $coupon,
        ]);
    }

    /**
     * Get coupon statistics
     */
    public function statistics(Coupon $coupon)
    {
        $totalBookings = $coupon->bookings()->count();
        $totalRevenue = $coupon->bookings()->sum('final_price');
        $totalDiscount = $coupon->bookings()->sum('discount');

        return response()->json([
            'success' => true,
            'data' => [
                'coupon' => $coupon,
                'total_bookings' => $totalBookings,
                'total_revenue' => $totalRevenue,
                'total_discount' => $totalDiscount,
                'remaining_usage' => $coupon->usage_limit ? max(0, $coupon->usage_limit - $coupon->used_count) : null,
            ],
        ]);
    }
}
