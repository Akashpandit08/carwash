<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Get available coupons for customer
     */
    public function index(Request $request)
    {
        $orderAmount = $request->input('order_amount', 0);
        $coupons = $this->couponService->getAvailableCoupons($orderAmount);

        return response()->json([
            'success' => true,
            'data' => $coupons,
        ]);
    }

    /**
     * Apply coupon to order
     */
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string',
            'order_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->couponService->validateAndCalculate(
            strtoupper($request->coupon_code),
            $request->order_amount
        );

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'coupon_id' => $result['coupon_id'],
                'original_amount' => $request->order_amount,
                'discount_amount' => $result['discount'],
                'final_amount' => $result['final_amount'],
            ],
        ]);
    }
}
