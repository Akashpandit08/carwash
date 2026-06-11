<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        protected RazorpayService $razorpayService
    ) {}

    /**
     * Initialize payment for a newly created booking.
     */
    public function initialize(Booking $booking, string $method): ?Payment
    {
        if ($method === 'cod') {
            return $this->initializeCod($booking);
        }

        return $this->initializeOnline($booking);
    }

    protected function initializeCod(Booking $booking): Payment
    {
        $booking->update([
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);

        return Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => $this->generatePaymentReference(),
            'method' => 'cod',
            'status' => 'pending',
            'amount' => $booking->final_price,
            'currency' => 'INR',
        ]);
    }

    protected function initializeOnline(Booking $booking): Payment
    {
        $booking->update([
            'payment_method' => 'online',
            'payment_status' => 'pending',
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => $this->generatePaymentReference(),
            'method' => 'online',
            'status' => 'pending',
            'amount' => $booking->final_price,
            'currency' => 'INR',
        ]);

        $orderData = $this->razorpayService->createOrder($booking, $payment);

        $payment->update([
            'gateway_order_id' => $orderData['order_id'],
            'razorpay_order_id' => $orderData['order_id'],
            'gateway_response' => $orderData,
        ]);

        return $payment->fresh();
    }

    /**
     * Get Razorpay checkout data for an online payment.
     */
    public function getCheckoutData(Payment $payment): array
    {
        $payment->load('booking.user');

        return $payment->gateway_response ?? $this->razorpayService->createOrder(
            $payment->booking,
            $payment
        );
    }

    /**
     * Mark payment and booking as paid after successful gateway callback.
     */
    public function markAsPaid(Payment $payment, array $gatewayData = []): Payment
    {
        return DB::transaction(function () use ($payment, $gatewayData) {
            $payment->update([
                'status' => 'paid',
                'gateway_payment_id' => $gatewayData['razorpay_payment_id'] ?? $gatewayData['payment_id'] ?? null,
                'razorpay_payment_id' => $gatewayData['razorpay_payment_id'] ?? null,
                'razorpay_signature' => $gatewayData['razorpay_signature'] ?? null,
                'gateway_response' => array_merge($payment->gateway_response ?? [], $gatewayData),
                'paid_at' => now(),
            ]);

            $payment->booking->update([
                'payment_status' => 'paid',
            ]);

            return $payment->fresh(['booking']);
        });
    }

    /**
     * Mark payment and booking as failed after gateway callback.
     */
    public function markAsFailed(Payment $payment, array $gatewayData = []): Payment
    {
        return DB::transaction(function () use ($payment, $gatewayData) {
            $payment->update([
                'status' => 'failed',
                'gateway_response' => array_merge($payment->gateway_response ?? [], $gatewayData),
            ]);

            $payment->booking->update([
                'payment_status' => 'failed',
            ]);

            return $payment->fresh(['booking']);
        });
    }

    public function syncBookingPaymentStatus(Booking $booking, string $status): Payment
    {
        return DB::transaction(function () use ($booking, $status) {
            $payment = $booking->latestPayment;

            if (!$payment) {
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'payment_reference' => $this->generatePaymentReference(),
                    'method' => $booking->payment_method,
                    'status' => 'pending',
                    'amount' => $booking->final_price,
                    'currency' => 'INR',
                ]);
            }

            $payment->update([
                'status' => $status,
                'paid_at' => $status === 'paid' ? now() : null,
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'source' => 'admin_manual_update',
                    'updated_at' => now()->toIso8601String(),
                ]),
            ]);

            $booking->update(['payment_status' => $status]);

            return $payment->fresh(['booking']);
        });
    }

    public function generatePaymentReference(): string
    {
        do {
            $reference = 'PAY-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8));
        } while (Payment::where('payment_reference', $reference)->exists());

        return $reference;
    }
}
