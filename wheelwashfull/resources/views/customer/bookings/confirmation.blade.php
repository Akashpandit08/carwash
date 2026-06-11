@extends('customer.layouts.app')

@section('title', 'Booking Confirmed - WashMate')
@section('header-title', 'Confirmed!')
@section('header-subtitle', 'Booking #' . str_pad($booking->id, 6, '0', STR_PAD_LEFT))

@section('content')
<div class="mt-2">
    {{-- Success Animation --}}
    <div class="text-center py-4">
        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
             style="width:90px;height:90px;">
            <i class="bi bi-check-circle-fill text-success" style="font-size:52px;"></i>
        </div>
        <h4 class="fw-bold mb-1">Booking Confirmed!</h4>
        <p class="text-muted" style="font-size:14px;">Your car wash has been scheduled</p>
        <div class="badge bg-primary px-3 py-2" style="font-size:16px;letter-spacing:1px;">
            #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
        </div>
    </div>

    {{-- Schedule Card --}}
    <div class="card" style="border-left:4px solid #4361ee;">
        <div class="card-body">
            <div class="row g-3 text-center">
                <div class="col-4 border-end">
                    <i class="bi bi-calendar-event text-primary d-block mb-1" style="font-size:20px;"></i>
                    <div class="fw-bold" style="font-size:13px;">{{ $booking->booking_date->format('d M') }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ $booking->booking_date->format('Y') }}</div>
                </div>
                <div class="col-4 border-end">
                    <i class="bi bi-clock text-primary d-block mb-1" style="font-size:20px;"></i>
                    <div class="fw-bold" style="font-size:13px;">{{ date('h:i', strtotime($booking->slot_time)) }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ date('A', strtotime($booking->slot_time)) }}</div>
                </div>
                <div class="col-4">
                    <i class="bi bi-currency-rupee text-primary d-block mb-1" style="font-size:20px;"></i>
                    <div class="fw-bold" style="font-size:13px;">₹{{ number_format($booking->final_price, 0) }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ strtoupper($booking->payment_method) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="card">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Booking Details</h6>
            @php
                $rows = [
                    ['Service', $booking->service->name],
                    ['Vehicle', $booking->vehicle->brand . ' ' . $booking->vehicle->model],
                    ['Reg. No.', strtoupper($booking->vehicle->registration_number)],
                    ['Address', $booking->address],
                ];
            @endphp
            @foreach($rows as [$label, $value])
            <div class="d-flex justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                <span class="text-muted" style="font-size:13px;">{{ $label }}</span>
                <span class="fw-semibold text-end" style="font-size:13px;max-width:60%;">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Payment --}}
    <div class="card">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Payment Summary</h6>
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted" style="font-size:13px;">Service Price</span>
                <span style="font-size:13px;">₹{{ number_format($booking->price, 0) }}</span>
            </div>
            @if($booking->discount > 0)
            <div class="d-flex justify-content-between py-1 text-success">
                <span style="font-size:13px;"><i class="bi bi-tag-fill me-1"></i>Discount</span>
                <span style="font-size:13px;">−₹{{ number_format($booking->discount, 0) }}</span>
            </div>
            @endif
            <div class="d-flex justify-content-between py-2 border-top mt-1">
                <span class="fw-bold">{{ $booking->payment_status === 'paid' ? 'Total Paid' : 'Total Amount' }}</span>
                <span class="fw-bold text-primary fs-6">₹{{ number_format($booking->final_price, 0) }}</span>
            </div>
            @php
                $paymentStatusColor = match($booking->payment_status) {
                    'paid' => 'success',
                    'failed' => 'danger',
                    'refunded' => 'secondary',
                    default => 'warning',
                };
            @endphp
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted" style="font-size:13px;">Payment Method</span>
                <span style="font-size:13px;">{{ strtoupper($booking->payment_method) }}</span>
            </div>
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted" style="font-size:13px;">Payment Status</span>
                <span class="badge bg-{{ $paymentStatusColor }}">
                    {{ ucfirst($booking->payment_status) }}
                </span>
            </div>
        </div>
    </div>

    <div class="alert alert-info d-flex gap-2 align-items-start">
        <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
        <span style="font-size:13px;">A partner will be assigned shortly and will arrive at your address on time.</span>
    </div>

    <div class="d-grid gap-2 mb-3">
        <a href="{{ route('customer.bookings.show', $booking) }}" class="btn btn-primary">
            View Booking Details
        </a>
        <a href="{{ route('customer.home') }}" class="btn btn-outline-secondary">
            <i class="bi bi-house me-1"></i>Back to Home
        </a>
    </div>
</div>
@endsection
