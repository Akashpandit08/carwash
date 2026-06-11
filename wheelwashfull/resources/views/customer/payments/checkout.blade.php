@extends('customer.layouts.app')

@section('title', 'Complete Payment - WashMate')
@section('header-title', 'Complete Payment')
@section('header-subtitle', $payment->payment_reference)

@section('content')
<div class="mt-2">
    <div class="card text-center" style="background:linear-gradient(135deg,#4361ee,#3f37c9);">
        <div class="card-body py-4 text-white">
            <p class="mb-1 opacity-75" style="font-size:13px;">Amount to Pay</p>
            <h1 class="fw-bold mb-0">₹{{ number_format($payment->amount, 0) }}</h1>
            <p class="mb-0 opacity-75 mt-1" style="font-size:12px;">{{ $payment->booking->service->name }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Booking Summary</h6>
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted" style="font-size:13px;">Booking #</span>
                <span style="font-size:13px;">{{ $payment->booking->booking_number }}</span>
            </div>
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted" style="font-size:13px;">Payment Reference</span>
                <span style="font-size:13px;">{{ $payment->payment_reference }}</span>
            </div>
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted" style="font-size:13px;">Gateway Order</span>
                <span class="text-truncate ms-2" style="font-size:12px;max-width:55%;">{{ $checkoutData['order_id'] ?? '—' }}</span>
            </div>
        </div>
    </div>

    <div class="alert alert-warning d-flex gap-2 align-items-start">
        <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
        <span style="font-size:13px;">Razorpay integration is in placeholder mode. Use the buttons below to simulate payment outcomes until live credentials are configured.</span>
    </div>

    <div class="d-grid gap-2 mb-3">
        <form action="{{ route('customer.payments.success', $payment) }}" method="POST">
            @csrf
            <input type="hidden" name="payment_id" value="pay_placeholder_{{ $payment->id }}">
            <button type="submit" class="btn btn-success btn-lg w-100">
                <i class="bi bi-credit-card me-2"></i>Pay with Razorpay
            </button>
        </form>

        <form action="{{ route('customer.payments.failed', $payment) }}" method="POST">
            @csrf
            <input type="hidden" name="reason" value="payment_cancelled">
            <button type="submit" class="btn btn-outline-danger">
                Simulate Payment Failure
            </button>
        </form>

        <a href="{{ route('customer.bookings.show', $payment->booking_id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Booking
        </a>
    </div>
</div>
@endsection
