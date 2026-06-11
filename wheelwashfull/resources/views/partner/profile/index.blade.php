@extends('partner.layouts.app')

@section('title', 'Profile - WashMate Partner')
@section('header-title', 'Profile')
@section('header-subtitle', auth()->user()->name)

@section('content')
<div class="card text-center">
    <div class="card-body py-4">
        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
            <i class="bi bi-person-fill text-success" style="font-size:40px;"></i>
        </div>
        <h5 class="fw-bold mb-0">{{ auth()->user()->name }}</h5>
        <p class="text-muted mb-0" style="font-size:13px;">Partner · {{ auth()->user()->mobile_number }}</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Edit Profile</h6>
        <form action="{{ route('partner.profile.update') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Mobile</label>
                <input type="text" class="form-control" value="{{ auth()->user()->mobile_number }}" disabled>
            </div>
            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
    </div>
</div>

<form action="{{ route('partner.logout') }}" method="POST" class="d-grid mb-3">
    @csrf
    <button type="submit" class="btn btn-outline-danger">
        <i class="bi bi-box-arrow-right me-2"></i>Logout
    </button>
</form>
@endsection
