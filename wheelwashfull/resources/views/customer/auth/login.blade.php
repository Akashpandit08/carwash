@extends('customer.layouts.app')

@section('title', 'Login - WashMate')

@section('content')
<div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px 0; margin-top:-14px; background:linear-gradient(160deg,#4361ee 0%,#3f37c9 60%,#f4f6fb 60%);">
    <div style="width:100%;max-width:400px;padding:0 16px;">
        <div class="text-center mb-4">
            <div class="bg-white rounded-circle p-3 d-inline-flex mb-3" style="box-shadow:0 4px 20px rgba(0,0,0,.15);">
                <i class="bi bi-droplet-fill" style="font-size:48px;color:#4361ee;"></i>
            </div>
            <h2 class="fw-bold text-white mb-1">WashMate</h2>
            <p class="mb-0" style="color:rgba(255,255,255,.8);font-size:14px;">Premium Car Wash at Your Doorstep</p>
        </div>

        <div class="card" style="border-radius:20px;">
            <div class="card-body p-4">
                <div id="mobile-step">
                    <h5 class="fw-bold mb-1">Welcome!</h5>
                    <p class="text-muted mb-4" style="font-size:13px;">Enter your mobile number to continue</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mobile Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-3">+91</span>
                            <input type="tel" class="form-control border-start-0" id="mobile_number"
                                   placeholder="10-digit mobile number" maxlength="15" inputmode="numeric"
                                   style="border-radius:0 10px 10px 0;">
                        </div>
                        <div class="text-danger small mt-1" id="mobile-error"></div>
                    </div>
                    <button class="btn btn-primary w-100" id="send-otp-btn" onclick="sendOtp()">
                        <span class="btn-label">Send OTP</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>

                <div id="otp-step" class="d-none">
                    <h5 class="fw-bold mb-1">Verify OTP</h5>
                    <p class="text-muted mb-4" style="font-size:13px;">
                        OTP sent to <strong id="display-mobile"></strong>
                        <button type="button" class="btn btn-link btn-sm p-0 ms-1" onclick="changeNumber()">Change</button>
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Enter 6-digit OTP</label>
                        <input type="text" class="form-control text-center fw-bold" id="otp"
                               placeholder="• • • • • •" maxlength="6" inputmode="numeric"
                               style="font-size:22px; letter-spacing:6px;">
                        <div class="text-danger small mt-1" id="otp-error"></div>
                        <div class="text-success small mt-1" id="dev-otp-hint"></div>
                    </div>
                    <button class="btn btn-primary w-100 mb-2" id="verify-btn" onclick="verifyOtp()">
                        <span class="btn-label">Verify & Login</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary w-100" id="resend-btn" onclick="resendOtp()">
                        Resend OTP <span id="resend-timer" class="text-muted"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let mobileNumber = '';
let resendCountdown;

function setLoading(btnId, loading) {
    const btn = document.getElementById(btnId);
    btn.querySelector('.btn-label').classList.toggle('d-none', loading);
    btn.querySelector('.spinner-border').classList.toggle('d-none', !loading);
    btn.disabled = loading;
}

function sendOtp() {
    mobileNumber = document.getElementById('mobile_number').value.trim();
    document.getElementById('mobile-error').textContent = '';

    if (!/^[0-9]{10,15}$/.test(mobileNumber)) {
        document.getElementById('mobile-error').textContent = 'Enter a valid mobile number (10-15 digits)';
        return;
    }

    setLoading('send-otp-btn', true);

    fetch('{{ route("customer.send-otp") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ mobile_number: mobileNumber })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('display-mobile').textContent = mobileNumber;
            document.getElementById('mobile-step').classList.add('d-none');
            document.getElementById('otp-step').classList.remove('d-none');
            if (data.otp) {
                document.getElementById('dev-otp-hint').innerHTML =
                    '<i class="bi bi-info-circle"></i> Dev OTP: <strong>' + data.otp + '</strong>';
            }
            startResendTimer();
            document.getElementById('otp').focus();
        } else {
            document.getElementById('mobile-error').textContent = data.message || 'Failed to send OTP';
        }
    })
    .catch(() => { document.getElementById('mobile-error').textContent = 'Network error. Try again.'; })
    .finally(() => setLoading('send-otp-btn', false));
}

function verifyOtp() {
    const otp = document.getElementById('otp').value.trim();
    document.getElementById('otp-error').textContent = '';

    if (!/^[0-9]{6}$/.test(otp)) {
        document.getElementById('otp-error').textContent = 'Enter a valid 6-digit OTP';
        return;
    }

    setLoading('verify-btn', true);

    fetch('{{ route("customer.verify-otp") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ mobile_number: mobileNumber, otp })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            document.getElementById('otp-error').textContent = data.message || 'Invalid OTP';
            setLoading('verify-btn', false);
        }
    })
    .catch(() => {
        document.getElementById('otp-error').textContent = 'Network error. Try again.';
        setLoading('verify-btn', false);
    });
}

function resendOtp() {
    document.getElementById('otp').value = '';
    document.getElementById('otp-error').textContent = '';
    document.getElementById('dev-otp-hint').textContent = '';
    document.getElementById('mobile-step').classList.remove('d-none');
    document.getElementById('otp-step').classList.add('d-none');
    clearInterval(resendCountdown);
}

function changeNumber() {
    document.getElementById('mobile-step').classList.remove('d-none');
    document.getElementById('otp-step').classList.add('d-none');
    document.getElementById('otp').value = '';
    clearInterval(resendCountdown);
}

function startResendTimer() {
    let seconds = 30;
    const timerEl = document.getElementById('resend-timer');
    const btn = document.getElementById('resend-btn');
    btn.disabled = true;
    resendCountdown = setInterval(() => {
        timerEl.textContent = '(' + seconds + 's)';
        seconds--;
        if (seconds < 0) {
            clearInterval(resendCountdown);
            timerEl.textContent = '';
            btn.disabled = false;
        }
    }, 1000);
}

document.getElementById('otp').addEventListener('keyup', e => {
    if (e.key === 'Enter') verifyOtp();
});
document.getElementById('mobile_number').addEventListener('keyup', e => {
    if (e.key === 'Enter') sendOtp();
});
</script>
@endpush
@endsection
