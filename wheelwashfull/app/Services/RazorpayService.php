<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;

/**
 * Placeholder for Razorpay integration.
 * Replace mock methods with real Razorpay SDK calls when credentials are configured.
 */
class RazorpayService
{
    public function isConfigured(): bool
    {
        return !empty(config('services.razorpay.key'))
            && !empty(config('services.razorpay.secret'));
    }

    /**
     * Create a Razorpay order for the given payment.
     */
    public function createOrder(Booking $booking, Payment $payment): array
    {
        $amount = (int) ($payment->amount * 100);
        $receipt = $payment->payment_reference;
        $orderId = 'order_placeholder_' . $receipt;

        if ($this->isConfigured()) {
            $response = Http::withBasicAuth(config('services.razorpay.key'), config('services.razorpay.secret'))
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount' => $amount,
                    'currency' => $payment->currency,
                    'receipt' => $receipt,
                ]);

            if ($response->successful()) {
                $orderId = $response->json('id');
            } else {
                \Illuminate\Support\Facades\Log::error("Razorpay Create Order Error: " . $response->body());
            }
        }

        return [
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => $payment->currency,
            'key' => config('services.razorpay.key', 'rzp_test_placeholder'),
            'name' => config('app.name', 'WashMate'),
            'description' => 'Booking #' . $booking->booking_number,
            'prefill' => [
                'name' => $booking->user->name ?? '',
                'contact' => $booking->user->mobile_number ?? '',
            ],
            'notes' => [
                'booking_id' => $booking->id,
                'payment_reference' => $payment->payment_reference,
            ],
        ];
    }

    /**
     * Verify payment signature from Razorpay callback.
     */
    public function verifyPaymentSignature(array $payload): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $orderId = $payload['razorpay_order_id'] ?? null;
        $paymentId = $payload['razorpay_payment_id'] ?? null;
        $signature = $payload['razorpay_signature'] ?? null;

        if (!$orderId || !$paymentId || !$signature) {
            return false;
        }

        $expectedSignature = hash_hmac(
            'sha256',
            $orderId . '|' . $paymentId,
            config('services.razorpay.secret')
        );

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify webhook signature from Razorpay callback.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSecret = config('services.razorpay.webhook_secret');
        if (!$webhookSecret) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }
}
