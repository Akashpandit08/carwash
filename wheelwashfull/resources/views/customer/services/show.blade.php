@extends('customer.layouts.app')

@section('title', $service->name . ' - WashMate')
@section('header-title', $service->name)
@section('header-subtitle', $service->category->name)
@section('back-url', route('customer.services.index'))

@section('content')
<div class="mt-2">
    <div class="card">
        <div class="card-body text-center py-4">
            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:80px;height:80px;">
                <i class="bi bi-droplet-fill text-primary" style="font-size:40px;"></i>
            </div>
            <h5 class="fw-bold mb-2">{{ $service->name }}</h5>
            <p class="text-muted mb-0" style="font-size:14px;">{{ $service->description }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Service Details</h6>

            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="text-muted d-flex align-items-center gap-2">
                    <i class="bi bi-tag-fill text-primary"></i> Price
                </span>
                <span class="fw-bold text-primary fs-5">₹{{ number_format($service->price, 0) }}</span>
            </div>

            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="text-muted d-flex align-items-center gap-2">
                    <i class="bi bi-clock-fill text-primary"></i> Duration
                </span>
                <span class="fw-bold">{{ $service->duration_minutes }} minutes</span>
            </div>

            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="text-muted d-flex align-items-center gap-2">
                    <i class="bi bi-grid-fill text-primary"></i> Category
                </span>
                <span class="fw-bold">{{ $service->category->name }}</span>
            </div>

            @if($service->vehicle_types)
            <div class="pt-2">
                <span class="text-muted d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-car-front-fill text-primary"></i> Suitable For
                </span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($service->vehicle_types as $type)
                        <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:12px;">{{ ucfirst($type) }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="card bg-light border-0">
        <div class="card-body py-3">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-shield-check text-success mt-1"></i>
                <div style="font-size:13px;">
                    <strong>What's included:</strong> Professional cleaning, eco-friendly products, trained staff at your doorstep.
                </div>
            </div>
        </div>
    </div>

    <div class="d-grid mt-2 mb-3">
        <a href="{{ route('customer.bookings.create', $service) }}" class="btn btn-primary btn-lg">
            <i class="bi bi-calendar-check me-2"></i>Book Now — ₹{{ number_format($service->price, 0) }}
        </a>
    </div>
</div>
@endsection
