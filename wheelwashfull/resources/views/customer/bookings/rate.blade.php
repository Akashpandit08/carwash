@extends('customer.layouts.app')

@section('title', 'Rate Service - WashMate')
@section('header-title', 'Rate Service')
@section('header-subtitle', 'Share your experience')
@section('back-url', route('customer.bookings.show', $booking))

@section('content')
<div class="mt-2">
    {{-- Booking Info --}}
    <div class="card">
        <div class="card-body py-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:46px;height:46px;">
                    <i class="bi bi-check-circle-fill text-success" style="font-size:22px;"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-bold text-truncate">{{ $booking->service->name }}</div>
                    <div class="text-muted" style="font-size:12px;">
                        {{ $booking->vehicle->brand }} {{ $booking->vehicle->model }}
                        &nbsp;•&nbsp;{{ $booking->booking_date->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('customer.bookings.store-rating', $booking) }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body text-center py-4">
                <h6 class="fw-bold mb-1">How was the service?</h6>
                <p class="text-muted mb-4" style="font-size:13px;">Tap a star to rate</p>

                <input type="hidden" name="rating" id="rating-input" value="{{ old('rating', 0) }}">

                <div class="d-flex justify-content-center gap-2 mb-2" id="stars">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star rating-star"
                           data-val="{{ $i }}"
                           style="font-size:44px;cursor:pointer;color:#ddd;transition:color .15s;"></i>
                    @endfor
                </div>
                <div id="rating-label" class="fw-semibold text-muted mb-0" style="font-size:15px;min-height:22px;"></div>

                @error('rating')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <label class="form-label fw-semibold">Write a Review <span class="text-muted fw-normal">(optional)</span></label>
                <textarea class="form-control @error('review') is-invalid @enderror"
                          name="review" rows="4"
                          placeholder="Tell us about your experience...">{{ old('review') }}</textarea>
                @error('review')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="text-end mt-1 text-muted" style="font-size:11px;" id="char-count">0 / 1000</div>
            </div>
        </div>

        <div class="d-grid gap-2 mb-3">
            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn" disabled>
                <i class="bi bi-check-circle me-2"></i>Submit Rating
            </button>
            <a href="{{ route('customer.bookings.show', $booking) }}" class="btn btn-outline-secondary">Skip</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const labels = { 1: '😞 Poor', 2: '😐 Fair', 3: '🙂 Good', 4: '😊 Very Good', 5: '🤩 Excellent' };
const stars = document.querySelectorAll('.rating-star');
const input = document.getElementById('rating-input');
const labelEl = document.getElementById('rating-label');
const submitBtn = document.getElementById('submit-btn');

function setRating(val) {
    input.value = val;
    stars.forEach((s, i) => {
        s.className = 'bi bi-star' + (i < val ? '-fill' : '') + ' rating-star';
        s.style.color = i < val ? '#ffd60a' : '#ddd';
    });
    labelEl.textContent = labels[val] || '';
    labelEl.style.color = val >= 4 ? '#06d6a0' : val === 3 ? '#4361ee' : '#ef476f';
    submitBtn.disabled = val === 0;
}

stars.forEach(star => {
    star.addEventListener('click', () => setRating(+star.dataset.val));
    star.addEventListener('mouseenter', () => {
        const v = +star.dataset.val;
        stars.forEach((s, i) => s.style.color = i < v ? '#ffd60a' : '#ddd');
    });
    star.addEventListener('mouseleave', () => setRating(+input.value));
});

const oldVal = parseInt(input.value);
if (oldVal > 0) setRating(oldVal);

const textarea = document.querySelector('textarea[name="review"]');
const counter = document.getElementById('char-count');
textarea.addEventListener('input', () => {
    counter.textContent = textarea.value.length + ' / 1000';
});
</script>
@endpush
