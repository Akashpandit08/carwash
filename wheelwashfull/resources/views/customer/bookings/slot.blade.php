@extends('customer.layouts.app')

@section('title', 'Select Slot - WashMate')
@section('header-title', 'Select Slot')
@section('header-subtitle', 'Choose date & time')

@section('content')
<div class="mt-2">
    {{-- Summary Bar --}}
    <div class="card bg-light border-0 mb-3">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-center">
                <div style="font-size:13px;">
                    <span class="fw-semibold">{{ $service->name }}</span>
                    <span class="text-muted ms-2">•</span>
                    <span class="text-muted ms-2">{{ $vehicle->brand }} {{ $vehicle->model }}</span>
                </div>
                <span class="fw-bold text-primary">₹{{ number_format($service->price, 0) }}</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('customer.bookings.apply-coupon') }}" method="POST" id="slotForm">
                @csrf
                <input type="hidden" name="service_id" id="service_id" value="{{ $service->id }}">
                <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                <input type="hidden" name="address" value="{{ session('booking_data.address') }}">

                <div class="mb-4">
                    <label class="form-label fw-semibold">Select Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('booking_date') is-invalid @enderror"
                           name="booking_date" id="booking_date" min="{{ date('Y-m-d') }}"
                           value="{{ old('booking_date', date('Y-m-d')) }}" required>
                    @error('booking_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Select Time Slot <span class="text-danger">*</span></label>
                    
                    <div id="slotsContainer" class="d-flex flex-wrap gap-2">
                        <!-- Slots will be populated here via AJAX -->
                        <div class="text-muted small">Select a date to view available slots...</div>
                    </div>

                    @error('slot_time')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary" id="continueBtn" disabled>
                        Continue <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('booking_date');
    const serviceId = document.getElementById('service_id').value;
    const slotsContainer = document.getElementById('slotsContainer');
    const continueBtn = document.getElementById('continueBtn');
    const form = document.getElementById('slotForm');

    function fetchSlots(date) {
        slotsContainer.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="ms-2 small text-muted">Loading slots...</span>';
        continueBtn.disabled = true;

        fetch(`{{ route('customer.bookings.slots.ajax') }}?date=${date}&service_id=${serviceId}`)
            .then(response => response.json())
            .then(data => {
                slotsContainer.innerHTML = '';
                if(data.success && data.slots.length > 0) {
                    data.slots.forEach((slot, index) => {
                        // slot.time is 'HH:mm'
                        let timeParts = slot.time.split(':');
                        let dateObj = new Date();
                        dateObj.setHours(parseInt(timeParts[0]));
                        dateObj.setMinutes(parseInt(timeParts[1]));
                        let formattedTime = dateObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                        let div = document.createElement('div');
                        let inputId = 'slot_' + index;
                        
                        div.innerHTML = `
                            <input type="radio" class="btn-check" name="slot_time" id="${inputId}" value="${slot.time}" required>
                            <label class="btn btn-outline-primary btn-sm d-flex flex-column align-items-center justify-content-center" for="${inputId}" style="border-radius:8px; min-width:85px; padding: 8px;">
                                <span class="fw-semibold">${formattedTime}</span>
                                <span class="badge bg-light text-dark mt-1" style="font-size:10px;">${slot.available_count} left</span>
                            </label>
                        `;
                        slotsContainer.appendChild(div);

                        // Listen for selection to enable Continue button
                        div.querySelector('input').addEventListener('change', function() {
                            continueBtn.disabled = false;
                        });
                    });
                } else {
                    slotsContainer.innerHTML = '<div class="alert alert-warning py-2 mb-0 w-100 small">No available slots for the selected date. Please choose another date.</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching slots:', error);
                slotsContainer.innerHTML = '<div class="text-danger small">Failed to load slots. Please try again.</div>';
            });
    }

    // Initial fetch
    if (dateInput.value) {
        fetchSlots(dateInput.value);
    }

    // On change fetch
    dateInput.addEventListener('change', function() {
        if(this.value) {
            fetchSlots(this.value);
        }
    });
});
</script>
@endpush
