<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     * Validate and calculate coupon discount
     *
     * @param string $couponCode
     * @param float $orderAmount
     * @return array
     */
    public function validateAndCalculate(string $couponCode, float $orderAmount): array
    {
        $coupon = Coupon::where('code', $couponCode)->first();

        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'Invalid coupon code',
                'discount' => 0,
                'final_amount' => $orderAmount,
            ];
        }

        // Check if coupon is active
        if (!$coupon->is_active) {
            return [
                'valid' => false,
                'message' => 'Coupon is not active',
                'discount' => 0,
                'final_amount' => $orderAmount,
            ];
        }

        // Check validity dates
        $now = now();
        if ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            return [
                'valid' => false,
                'message' => 'Coupon is not yet valid',
                'discount' => 0,
                'final_amount' => $orderAmount,
            ];
        }

        if ($coupon->valid_until && $now->gt($coupon->valid_until)) {
            return [
                'valid' => false,
                'message' => 'Coupon has expired',
                'discount' => 0,
                'final_amount' => $orderAmount,
            ];
        }

        // Check usage limit
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return [
                'valid' => false,
                'message' => 'Coupon usage limit exceeded',
                'discount' => 0,
                'final_amount' => $orderAmount,
            ];
        }

        // Check minimum order amount
        if ($coupon->min_order_amount && $orderAmount < $coupon->min_order_amount) {
            return [
                'valid' => false,
                'message' => "Minimum order amount of ₹{$coupon->min_order_amount} required",
                'discount' => 0,
                'final_amount' => $orderAmount,
            ];
        }

        // Calculate discount
        $discount = $this->calculateDiscount($coupon, $orderAmount);

        return [
            'valid' => true,
            'message' => 'Coupon applied successfully',
            'coupon_id' => $coupon->id,
            'discount' => $discount,
            'final_amount' => max(0, $orderAmount - $discount),
        ];
    }

    /**
     * Calculate discount based on coupon type
     *
     * @param Coupon $coupon
     * @param float $amount
     * @return float
     */
    private function calculateDiscount(Coupon $coupon, float $amount): float
    {
        $discount = 0;

        if ($coupon->discount_type === 'percentage') {
            $discount = ($amount * $coupon->discount_value) / 100;

            // Apply maximum discount cap for percentage discounts
            if ($coupon->max_discount && $discount > $coupon->max_discount) {
                $discount = $coupon->max_discount;
            }
        } else {
            // Fixed discount
            $discount = $coupon->discount_value;
        }

        // Ensure discount doesn't exceed the order amount
        return min($discount, $amount);
    }

    /**
     * Increment coupon usage count
     *
     * @param int $couponId
     * @return void
     */
    public function incrementUsage(int $couponId): void
    {
        DB::table('coupons')
            ->where('id', $couponId)
            ->increment('used_count');
    }

    /**
     * Decrement coupon usage count (for cancelled bookings)
     *
     * @param int $couponId
     * @return void
     */
    public function decrementUsage(int $couponId): void
    {
        DB::table('coupons')
            ->where('id', $couponId)
            ->where('used_count', '>', 0)
            ->decrement('used_count');
    }

    /**
     * Get available coupons for customer
     *
     * @param float $orderAmount
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableCoupons(float $orderAmount = 0)
    {
        $now = now();

        $query = Coupon::where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereRaw('used_count < usage_limit');
            });

        if ($orderAmount > 0) {
            $query->where(function ($q) use ($orderAmount) {
                $q->whereNull('min_order_amount')
                    ->orWhere('min_order_amount', '<=', $orderAmount);
            });
        }

        return $query->orderBy('discount_value', 'desc')->get();
    }
}
