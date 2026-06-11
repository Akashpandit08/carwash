@extends('customer.layouts.app')

@section('title', 'Book Service - WashMate')
@section('header-title', 'Book Service')
@section('header-subtitle', $service->name)
@section('back-url', route('customer.services.show', $service))

@section('content')
<div class="mt-2">
    {{-- Service Summary --}}
    <div class="card" style="background:linear-gradient(135deg,#4361ee,#3f37c9);">
        <div class="card-body text-white py-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:50px;height:50px;">
                    <i class="bi bi-droplet-fill" style="font-size:24px;"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-0">{{ $service->name }}</h6>
                    <span style="font-size:12px;opacity:.85;">{{ $service->duration_minutes }} mins</span>
                </div>
                <div class="text-end">
                    <div class="fw-bold fs-5">₹{{ number_format($service->price, 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('customer.bookings.select-slot') }}" method="POST">
                @csrf
                <input type="hidden" name="service_id" value="{{ $service->id }}">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Vehicle <span class="text-danger">*</span></label>
                    @foreach($vehicles as $vehicle)
                    <div class="mb-2">
                        <input type="radio" class="btn-check" name="vehicle_id" id="veh_{{ $vehicle->id }}"
                               value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'checked' : ($loop->first ? 'checked' : '') }} required>
                        <label for="veh_{{ $vehicle->id }}" class="btn btn-outline-secondary w-100 text-start py-3 px-3" style="border-radius:12px;">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-car-front-fill text-primary" style="font-size:20px;"></i>
                                <div>
                                    <div class="fw-semibold" style="font-size:14px;">{{ $vehicle->brand }} {{ $vehicle->model }}</div>
                                    <div class="text-muted" style="font-size:12px;">{{ strtoupper($vehicle->registration_number) }} • {{ ucfirst($vehicle->vehicle_type) }}</div>
                                </div>
                            </div>
                        </label>
                    </div>
                    @endforeach
                    <a href="{{ route('customer.vehicles.create') }}" class="btn btn-outline-primary w-100 btn-sm mt-1" style="border-style:dashed;">
                        <i class="bi bi-plus-circle me-1"></i>Add Another Vehicle
                    </a>
                    @error('vehicle_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Service Address <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('address') is-invalid @enderror"
                              name="address" rows="3"
                              placeholder="House/Flat no, Street, Area, City">{{ old('address') }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Our partner will arrive at this address.</small>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        Select Date & Time <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                    <a href="{{ route('customer.services.show', $service) }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
