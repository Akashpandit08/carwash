<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected RazorpayService $razorpayService
    ) {}

    public function checkout(Payment $payment)
    {
        $this->authorizePayment($payment);

        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This payment has already been processed.',
            ], 422);
        }

        $payment->load(['booking.service']);

        return response()->json([
            'success' => true,
            'data' => [
                'payment' => $payment,
                'checkout' => $this->paymentService->getCheckoutData($payment),
            ],
        ]);
    }

    public function success(Request $request, Payment $payment)
    {
        $this->authorizePayment($payment);

        if ($payment->status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Payment already completed.',
                'data' => $payment->load('booking'),
            ]);
        }

        $gatewayData = $request->only([
            'razorpay_payment_id',
            'razorpay_order_id',
            'razorpay_signature',
        ]);

        if (!$this->razorpayService->verifyPaymentSignature($gatewayData)) {
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed.',
            ], 422);
        }

        $payment = $this->paymentService->markAsPaid($payment, $gatewayData);

        // Send Notification
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->sendPaymentSuccess(auth()->user(), $payment->booking);

        return response()->json([
            'success' => true,
            'message' => 'Payment completed successfully.',
            'data' => $payment,
        ]);
    }

    public function failed(Request $request, Payment $payment)
    {
        $this->authorizePayment($payment);

        if ($payment->status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Payment already completed.',
                'data' => $payment->load('booking'),
            ]);
        }

        $gatewayData = $request->only(['error_code', 'error_description', 'reason']);

        $payment = $this->paymentService->markAsFailed($payment, $gatewayData);

        return response()->json([
            'success' => false,
            'message' => 'Payment failed.',
            'data' => $payment,
        ], 422);
    }

    protected function authorizePayment(Payment $payment): void
    {
        $payment->load('booking');

        if ($payment->booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }
    }
    public function verify(Request $request, Payment $payment)
    {
        return $this->success($request, $payment);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        if (!$signature || !$this->razorpayService->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $data = json_decode($payload, true);
        if ($data['event'] === 'order.paid' || $data['event'] === 'payment.captured') {
            $paymentEntity = $data['payload']['payment']['entity'];
            $orderId = $paymentEntity['order_id'];

            $payment = Payment::with('booking.user')->where('razorpay_order_id', $orderId)->first();
            if ($payment && $payment->status !== 'paid') {
                $gatewayData = [
                    'razorpay_payment_id' => $paymentEntity['id'],
                    'razorpay_order_id' => $orderId,
                    'webhook_source' => true,
                ];
                $this->paymentService->markAsPaid($payment, $gatewayData);

                // Send Notification
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->sendPaymentSuccess($payment->booking->user, $payment->booking);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
