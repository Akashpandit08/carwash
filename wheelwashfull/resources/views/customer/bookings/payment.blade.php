@extends('customer.layouts.app')

@section('title', 'Payment - WashMate')
@section('header-title', 'Payment')
@section('header-subtitle', 'Choose payment method')

@section('content')
<div class="mt-2">
    {{-- Amount --}}
    <div class="card text-center" style="background:linear-gradient(135deg,#4361ee,#3f37c9);">
        <div class="card-body py-4 text-white">
            <p class="mb-1 opacity-75" style="font-size:13px;">Amount to Pay</p>
            <h1 class="fw-bold mb-0">₹{{ number_format($finalPrice, 0) }}</h1>
            <p class="mb-0 opacity-75 mt-1" style="font-size:12px;">{{ $service->name }}</p>
        </div>
    </div>

    <form action="{{ route('customer.bookings.store') }}" method="POST" id="payment-form">
        @csrf

        <h6 class="fw-bold mb-3">Select Payment Method</h6>

        <input type="radio" class="btn-check" name="payment_method" id="cod" value="cod"
               {{ old('payment_method', 'cod') == 'cod' ? 'checked' : '' }} required>
        <label for="cod" class="card mb-2 w-100 text-start" style="cursor:pointer;border:2px solid transparent;" id="lbl-cod">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:50px;height:50px;">
                        <i class="bi bi-cash-stack text-success" style="font-size:24px;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Cash on Delivery</div>
                        <div class="text-muted" style="font-size:12px;">Pay after service is completed</div>
                    </div>
                    <i class="bi bi-check-circle-fill text-primary cod-check" style="font-size:20px;display:none;"></i>
                </div>
            </div>
        </label>

        @if($isRazorpayConfigured)
        <input type="radio" class="btn-check" name="payment_method" id="online" value="online"
               {{ old('payment_method') == 'online' ? 'checked' : '' }}>
        <label for="online" class="card mb-3 w-100 text-start" style="cursor:pointer;border:2px solid transparent;" id="lbl-online">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:50px;height:50px;">
                        <i class="bi bi-credit-card text-primary" style="font-size:24px;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Online Payment</div>
                        <div class="text-muted" style="font-size:12px;">UPI, Card, Net Banking</div>
                    </div>
                    <i class="bi bi-check-circle-fill text-primary online-check" style="font-size:20px;display:none;"></i>
                </div>
            </div>
        </label>
        @endif

        @error('payment_method')
            <div class="text-danger small mb-3">{{ $message }}</div>
        @enderror

        <div class="card bg-light border-0 mb-3">
            <div class="card-body py-2 d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock-fill text-success"></i>
                <span style="font-size:12px;" class="text-muted">Your payment is 100% secure and encrypted.</span>
            </div>
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-check-circle me-2"></i>Confirm Booking
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function updatePaymentUI() {
    const codInput = document.getElementById('cod');
    if (!codInput) return;
    
    const cod = codInput.checked;
    const lblCod = document.getElementById('lbl-cod');
    if (lblCod) lblCod.style.borderColor = cod ? '#4361ee' : 'transparent';
    
    const codCheck = document.querySelector('.cod-check');
    if (codCheck) codCheck.style.display = cod ? '' : 'none';
    
    const onlineInput = document.getElementById('online');
    const lblOnline = document.getElementById('lbl-online');
    const onlineCheck = document.querySelector('.online-check');
    
    if (onlineInput && lblOnline && onlineCheck) {
        lblOnline.style.borderColor = !cod ? '#4361ee' : 'transparent';
        onlineCheck.style.display = !cod ? '' : 'none';
    }
}

const codInput = document.getElementById('cod');
if (codInput) {
    codInput.addEventListener('change', updatePaymentUI);
}

const onlineInput = document.getElementById('online');
if (onlineInput) {
    onlineInput.addEventListener('change', updatePaymentUI);
}

updatePaymentUI();
</script>
@endpush
