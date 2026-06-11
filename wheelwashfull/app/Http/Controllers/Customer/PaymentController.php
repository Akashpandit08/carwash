<?php

namespace App\Http\Controllers\Customer;

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
            return redirect()->route('customer.bookings.show', $payment->booking_id)
                ->with('info', 'This payment has already been processed.');
        }

        $payment->load(['booking.service']);
        $checkoutData = $this->paymentService->getCheckoutData($payment);

        return view('customer.payments.checkout', compact('payment', 'checkoutData'));
    }

    public function success(Request $request, Payment $payment)
    {
        $this->authorizePayment($payment);

        if ($payment->status === 'paid') {
            return redirect()->route('customer.bookings.confirmation', $payment->booking_id);
        }

        $gatewayData = $request->only([
            'razorpay_payment_id',
            'razorpay_order_id',
            'razorpay_signature',
        ]);

        if (!$this->razorpayService->verifyPaymentSignature($gatewayData)) {
            return redirect()->route('customer.bookings.show', $payment->booking_id)
                ->with('error', 'Payment verification failed. Please try again.');
        }

        $this->paymentService->markAsPaid($payment, $gatewayData);

        return redirect()->route('customer.bookings.confirmation', $payment->booking_id)
            ->with('success', 'Payment completed successfully.');
    }

    public function failed(Request $request, Payment $payment)
    {
        $this->authorizePayment($payment);

        if ($payment->status === 'paid') {
            return redirect()->route('customer.bookings.confirmation', $payment->booking_id);
        }

        $gatewayData = $request->only(['error_code', 'error_description', 'reason']);

        $this->paymentService->markAsFailed($payment, $gatewayData);

        return redirect()->route('customer.bookings.show', $payment->booking_id)
            ->with('error', 'Payment failed. Please try again or choose Cash on Delivery.');
    }

    protected function authorizePayment(Payment $payment): void
    {
        $payment->load('booking');

        if ($payment->booking->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
