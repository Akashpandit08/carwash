@extends('customer.layouts.app')

@section('title', 'Profile - WashMate')
@section('header-title', 'My Profile')
@section('header-subtitle', 'Manage your account')

@section('content')
<div class="mt-2">
    {{-- Avatar & Info --}}
    <div class="card text-center">
        <div class="card-body py-4">
            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:80px;height:80px;">
                <i class="bi bi-person-fill text-primary" style="font-size:42px;"></i>
            </div>
            <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
            <p class="text-muted mb-2" style="font-size:14px;">
                <i class="bi bi-phone me-1"></i>{{ $user->mobile_number }}
            </p>
            @if($user->email)
            <p class="text-muted mb-2" style="font-size:13px;">
                <i class="bi bi-envelope me-1"></i>{{ $user->email }}
            </p>
            @endif
            <span class="badge bg-primary px-3">{{ ucfirst($user->role) }}</span>
        </div>
    </div>

    {{-- Stats --}}
    <div class="card">
        <div class="card-body">
            <div class="row g-0 text-center">
                <div class="col-4 border-end">
                    <div class="fw-bold text-primary" style="font-size:22px;">{{ $user->bookings->count() }}</div>
                    <div class="text-muted" style="font-size:12px;">Total</div>
                </div>
                <div class="col-4 border-end">
                    <div class="fw-bold text-success" style="font-size:22px;">{{ $user->bookings->where('status', 'completed')->count() }}</div>
                    <div class="text-muted" style="font-size:12px;">Completed</div>
                </div>
                <div class="col-4">
                    <div class="fw-bold text-warning" style="font-size:22px;">{{ $user->vehicles->count() }}</div>
                    <div class="text-muted" style="font-size:12px;">Vehicles</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Profile --}}
    <div class="card">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Edit Profile</h6>
            <form action="{{ route('customer.profile.update') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email', $user->email) }}" placeholder="you@email.com">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Mobile Number</label>
                    <input type="text" class="form-control bg-light" value="{{ $user->mobile_number }}" readonly disabled>
                    <small class="text-muted">Mobile number cannot be changed</small>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle me-2"></i>Save Changes
                </button>
            </form>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="card">
        <div class="list-group list-group-flush" style="border-radius:14px;">
            <a href="{{ route('customer.vehicles.index') }}" class="list-group-item list-group-item-action border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-car-front text-primary me-3"></i>My Vehicles</span>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>
            </a>
            <a href="{{ route('customer.bookings.index') }}" class="list-group-item list-group-item-action border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-calendar-check text-primary me-3"></i>My Bookings</span>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>
            </a>
            <a href="{{ route('customer.services.index') }}" class="list-group-item list-group-item-action border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-droplet text-primary me-3"></i>Services</span>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>
            </a>
        </div>
    </div>

    {{-- Logout --}}
    <form action="{{ route('customer.logout') }}" method="POST" class="mb-3">
        @csrf
        <button type="submit" class="btn btn-outline-danger w-100">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
        </button>
    </form>
</div>
@endsection
