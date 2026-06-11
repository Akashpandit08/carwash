@extends('partner.layouts.app')

@section('title', 'Partner Login - WashMate')

@section('content')
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px 0;margin-top:-14px;background:linear-gradient(160deg,#059669 0%,#047857 60%,#f0fdf4 60%);">
    <div style="width:100%;max-width:400px;padding:0 16px;">
        <div class="text-center mb-4">
            <div class="bg-white rounded-circle p-3 d-inline-flex mb-3" style="box-shadow:0 4px 20px rgba(0,0,0,.15);">
                <i class="bi bi-person-badge-fill" style="font-size:48px;color:#059669;"></i>
            </div>
            <h2 class="fw-bold text-white mb-1">WashMate Partner</h2>
            <p class="mb-0" style="color:rgba(255,255,255,.8);font-size:14px;">Manage your jobs on the go</p>
        </div>

        <div class="card" style="border-radius:20px;">
            <div class="card-body p-4">
                <div id="mobile-step">
                    <h5 class="fw-bold mb-1">Partner Login</h5>
                    <p class="text-muted mb-4" style="font-size:13px;">Enter your registered mobile number</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mobile Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">+91</span>
                            <input type="tel" class="form-control border-start-0" id="mobile_number"
                                   placeholder="10-digit mobile number" maxlength="15" inputmode="numeric">
                        </div>
                        <div class="text-danger small mt-1" id="mobile-error"></div>
                    </div>
                    <button class="btn btn-primary w-100" id="send-otp-btn" onclick="sendOtp()">
                        <span class="btn-label">Send OTP</span>
                        <span class="spinner-border spinner-border-sm d-none"></span>
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
                               style="font-size:22px;letter-spacing:6px;">
                        <div class="text-danger small mt-1" id="otp-error"></div>
                        <div class="text-success small mt-1" id="dev-otp-hint"></div>
                    </div>
                    <button class="btn btn-primary w-100 mb-2" id="verify-btn" onclick="verifyOtp()">
                        <span class="btn-label">Verify & Login</span>
                        <span class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="sendOtp()">Resend OTP</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let mobileNumber = '';

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
        document.getElementById('mobile-error').textContent = 'Enter a valid mobile number';
        return;
    }
    setLoading('send-otp-btn', true);
    fetch('{{ route('partner.send-otp') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ mobile_number: mobileNumber })
    })
    .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
    .then(({ ok, data }) => {
        setLoading('send-otp-btn', false);
        if (!ok) {
            document.getElementById('mobile-error').textContent = data.message || 'Failed to send OTP';
            return;
        }
        document.getElementById('mobile-step').classList.add('d-none');
        document.getElementById('otp-step').classList.remove('d-none');
        document.getElementById('display-mobile').textContent = '+91 ' + mobileNumber;
        if (data.otp) document.getElementById('dev-otp-hint').textContent = 'Dev OTP: ' + data.otp;
    });
}

function verifyOtp() {
    const otp = document.getElementById('otp').value.trim();
    document.getElementById('otp-error').textContent = '';
    if (otp.length !== 6) {
        document.getElementById('otp-error').textContent = 'Enter 6-digit OTP';
        return;
    }
    setLoading('verify-btn', true);
    fetch('{{ route('partner.verify-otp') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ mobile_number: mobileNumber, otp })
    })
    .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
    .then(({ ok, data }) => {
        setLoading('verify-btn', false);
        if (!ok) {
            document.getElementById('otp-error').textContent = data.message || 'Verification failed';
            return;
        }
        window.location.href = data.redirect;
    });
}

function changeNumber() {
    document.getElementById('otp-step').classList.add('d-none');
    document.getElementById('mobile-step').classList.remove('d-none');
    document.getElementById('otp').value = '';
    document.getElementById('dev-otp-hint').textContent = '';
}
</script>
@endpush
@endsection
