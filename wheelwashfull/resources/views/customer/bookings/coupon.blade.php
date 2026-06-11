@extends('customer.layouts.app')

@section('title', 'Apply Coupon - WashMate')
@section('header-title', 'Apply Coupon')
@section('header-subtitle', 'Save more on your booking')

@section('content')
<div class="mt-2">
    {{-- Coupon Input --}}
    <div class="card">
        <div class="card-body">
            <label class="form-label fw-semibold mb-2">Have a coupon code?</label>
            <div class="input-group">
                <input type="text" class="form-control text-uppercase fw-semibold"
                       id="coupon_code" placeholder="Enter code" style="letter-spacing:1px;">
                <button class="btn btn-primary px-4" type="button" onclick="applyCoupon()">Apply</button>
            </div>
            <div id="coupon-msg" class="mt-2"></div>
        </div>
    </div>

    {{-- Available Coupons --}}
    @php $validCoupons = $coupons->filter(fn($c) => $c->isValid()); @endphp
    @if($validCoupons->count() > 0)
    <h6 class="fw-bold mb-2">Available Coupons</h6>
    @foreach($validCoupons as $coupon)
    <div class="card coupon-card" style="border-left:4px solid #4361ee;">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary" style="font-size:13px;letter-spacing:.5px;">{{ $coupon->code }}</span>
                        <span class="text-success fw-bold" style="font-size:13px;">
                            @if($coupon->discount_type === 'percentage')
                                {{ $coupon->discount_value }}% OFF
                            @else
                                ₹{{ number_format($coupon->discount_value, 0) }} OFF
                            @endif
                        </span>
                    </div>
                    @if($coupon->description)
                    <p class="text-muted mb-1" style="font-size:12px;">{{ $coupon->description }}</p>
                    @endif
                    @if($coupon->min_order_amount > 0)
                    <span class="text-muted" style="font-size:11px;">
                        <i class="bi bi-info-circle me-1"></i>Min. order ₹{{ number_format($coupon->min_order_amount, 0) }}
                    </span>
                    @endif
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm flex-shrink-0 ms-3"
                        onclick="document.getElementById('coupon_code').value='{{ $coupon->code }}'; applyCoupon();"
                        style="border-radius:20px;">
                    Apply
                </button>
            </div>
        </div>
    </div>
    @endforeach
    @endif

    {{-- Price Summary + Continue --}}
    <form action="{{ route('customer.bookings.select-payment') }}" method="POST" id="payment-form">
        @csrf
        <input type="hidden" name="discount" id="discount" value="0">
        <input type="hidden" name="coupon_id" id="coupon_id" value="">

        <div class="card bg-light border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Order Summary</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">{{ $service->name }}</span>
                    <span>₹{{ number_format($service->price, 0) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-success" id="discount-row" style="display:none !important;">
                    <span><i class="bi bi-tag-fill me-1"></i>Discount</span>
                    <span>− ₹<span id="discount-amount">0</span></span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span class="text-primary fs-5">₹<span id="final-price">{{ number_format($service->price, 0) }}</span></span>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2 mt-2 mb-3">
            <button type="submit" class="btn btn-primary">
                Continue to Payment <i class="bi bi-arrow-right ms-1"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="skipCoupon()">Skip</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const servicePrice = {{ $service->price }};

function applyCoupon() {
    const code = document.getElementById('coupon_code').value.trim().toUpperCase();
    const msg = document.getElementById('coupon-msg');

    if (!code) {
        msg.innerHTML = '<div class="alert alert-warning py-2 mb-0">Please enter a coupon code.</div>';
        return;
    }

    msg.innerHTML = '<div class="text-muted small">Checking...</div>';

    fetch('{{ route("customer.bookings.validate-coupon") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ coupon_code: code })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            msg.innerHTML = '<div class="alert alert-success py-2 mb-0"><i class="bi bi-check-circle me-1"></i>' + data.message + '</div>';
            document.getElementById('discount').value = data.discount;
            document.getElementById('coupon_id').value = data.coupon_id;
            document.getElementById('discount-amount').textContent = Math.round(data.discount).toLocaleString();
            document.getElementById('final-price').textContent = Math.round(data.final_price).toLocaleString();
            document.getElementById('discount-row').style.cssText = 'display:flex !important;';
        } else {
            msg.innerHTML = '<div class="alert alert-danger py-2 mb-0"><i class="bi bi-x-circle me-1"></i>' + data.message + '</div>';
            resetDiscount();
        }
    })
    .catch(() => {
        msg.innerHTML = '<div class="alert alert-danger py-2 mb-0">Network error. Try again.</div>';
    });
}

function resetDiscount() {
    document.getElementById('discount').value = 0;
    document.getElementById('coupon_id').value = '';
    document.getElementById('final-price').textContent = Math.round(servicePrice).toLocaleString();
    document.getElementById('discount-row').style.cssText = 'display:none !important;';
}

function skipCoupon() {
    resetDiscount();
    document.getElementById('payment-form').submit();
}
</script>
@endpush
