@extends('partner.layouts.app')

@section('title', 'Job Details - WashMate Partner')
@section('header-title', 'Job Details')
@section('header-subtitle', $booking->booking_number)
@section('back-url', url()->previous())

@section('content')
@php
    $statusColors = [
        'assigned' => 'warning', 'accepted' => 'info', 'on_the_way' => 'primary',
        'started' => 'primary', 'completed' => 'success', 'cancelled' => 'danger',
    ];
    $steps = ['assigned', 'accepted', 'on_the_way', 'started', 'completed'];
    $currentIndex = array_search($booking->status, $steps);
    $beforeImages = $booking->images->where('image_type', 'before');
    $afterImages = $booking->images->where('image_type', 'after');
@endphp

<div class="card bg-{{ $statusColors[$booking->status] ?? 'secondary' }} bg-opacity-10 border-0 mb-3">
    <div class="card-body py-3 text-center">
        <span class="badge bg-{{ $statusColors[$booking->status] ?? 'secondary' }} mb-2">
            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
        </span>
        <div class="d-flex justify-content-between px-1 mt-2">
            @foreach($steps as $i => $step)
            <div class="status-step text-center flex-fill {{ $i <= $currentIndex ? ($i === $currentIndex ? 'active' : 'done') : 'text-muted' }}">
                <i class="bi bi-{{ $i <= $currentIndex ? 'check-circle-fill' : 'circle' }} d-block" style="font-size:14px;"></i>
                {{ ucfirst(str_replace('_', ' ', $step)) }}
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Schedule</h6>
        <div class="row text-center">
            <div class="col-4 border-end">
                <div class="text-muted" style="font-size:11px;">DATE</div>
                <div class="fw-bold">{{ $booking->booking_date->format('d M') }}</div>
            </div>
            <div class="col-4 border-end">
                <div class="text-muted" style="font-size:11px;">TIME</div>
                <div class="fw-bold">{{ date('h:i A', strtotime($booking->slot_time)) }}</div>
            </div>
            <div class="col-4">
                <div class="text-muted" style="font-size:11px;">EARNINGS</div>
                <div class="fw-bold text-success">₹{{ number_format($booking->final_price, 0) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Customer & Vehicle</h6>
        <div class="mb-2"><i class="bi bi-person me-2"></i>{{ $booking->user->name ?? 'Customer' }}</div>
        <div class="mb-2"><i class="bi bi-telephone me-2"></i>{{ $booking->user->mobile_number ?? '—' }}</div>
        <div class="mb-2"><i class="bi bi-car-front me-2"></i>{{ $booking->vehicle->brand }} {{ $booking->vehicle->model }} ({{ strtoupper($booking->vehicle->registration_number) }})</div>
        <div><i class="bi bi-geo-alt me-2"></i>{{ $booking->address }}</div>
        @if($booking->address)
        <a href="https://maps.google.com/?q={{ urlencode($booking->address) }}" target="_blank" class="btn btn-outline-success btn-sm w-100 mt-3">
            <i class="bi bi-map me-1"></i>Open in Maps
        </a>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Service</h6>
        <div class="fw-semibold">{{ $booking->service->name }}</div>
        <div class="text-muted" style="font-size:12px;">{{ $booking->service->duration_minutes ?? '—' }} mins</div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Payment</h6>
        @php
            $paymentColor = match($booking->payment_status) {
                'paid' => 'success',
                'failed' => 'danger',
                'refunded' => 'secondary',
                default => 'warning',
            };
        @endphp
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted" style="font-size:13px;">Method</span>
            <span class="badge bg-{{ $booking->payment_method === 'cod' ? 'success' : 'primary' }}">
                {{ strtoupper($booking->payment_method) }}
            </span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted" style="font-size:13px;">Status</span>
            <span class="badge bg-{{ $paymentColor }}">{{ ucfirst($booking->payment_status) }}</span>
        </div>
        @if($booking->payment_method === 'cod' && $booking->payment_status !== 'paid' && in_array($booking->status, ['started', 'completed']))
        <form action="{{ route('partner.jobs.collect-cod', $booking) }}" method="POST" class="mt-3">
            @csrf
            <button type="submit" class="btn btn-success w-100">
                <i class="bi bi-cash-coin me-2"></i>Mark COD Collected
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Before images --}}
@if(in_array($booking->status, ['on_the_way', 'started', 'completed']) || $beforeImages->count())
<div class="card">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Before Images</h6>
        <div class="row g-2 mb-3">
            @forelse($beforeImages as $img)
            <div class="col-4">
                <img src="{{ Storage::url($img->image_path) }}" class="img-fluid rounded" style="height:80px;object-fit:cover;width:100%;">
            </div>
            @empty
            <div class="col-12 text-muted small">No before images yet</div>
            @endforelse
        </div>
        @if(in_array($booking->status, ['on_the_way', 'started']))
        <form action="{{ route('partner.jobs.upload-image', $booking) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="image_type" value="before">
            <input type="file" name="image" accept="image/*" capture="environment" class="form-control form-control-sm mb-2" required>
            <button type="submit" class="btn btn-outline-primary btn-sm w-100">Upload Before Image</button>
        </form>
        @endif
    </div>
</div>
@endif

{{-- After images --}}
@if(in_array($booking->status, ['started', 'completed']) || $afterImages->count())
<div class="card">
    <div class="card-body">
        <h6 class="fw-bold mb-3">After Images <span class="text-danger">*</span></h6>
        <div class="row g-2 mb-3">
            @forelse($afterImages as $img)
            <div class="col-4">
                <img src="{{ Storage::url($img->image_path) }}" class="img-fluid rounded" style="height:80px;object-fit:cover;width:100%;">
            </div>
            @empty
            <div class="col-12 text-muted small">Required before completing job</div>
            @endforelse
        </div>
        @if($booking->status === 'started')
        <form action="{{ route('partner.jobs.upload-image', $booking) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="image_type" value="after">
            <input type="file" name="image" accept="image/*" capture="environment" class="form-control form-control-sm mb-2" required>
            <button type="submit" class="btn btn-outline-primary btn-sm w-100">Upload After Image</button>
        </form>
        @endif
    </div>
</div>
@endif

{{-- Action buttons --}}
<div class="d-grid gap-2 mb-3">
    @if($booking->status === 'assigned')
    <form action="{{ route('partner.jobs.accept', $booking) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary btn-lg w-100"><i class="bi bi-check-circle me-2"></i>Accept Job</button>
    </form>
    @elseif($booking->status === 'accepted')
    <form action="{{ route('partner.jobs.on-the-way', $booking) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary btn-lg w-100"><i class="bi bi-geo-alt me-2"></i>Mark On The Way</button>
    </form>
    @elseif($booking->status === 'on_the_way')
    <form action="{{ route('partner.jobs.start', $booking) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary btn-lg w-100" {{ $beforeImages->isEmpty() ? 'disabled' : '' }}>
            <i class="bi bi-play-circle me-2"></i>Start Job
        </button>
    </form>
    @if($beforeImages->isEmpty())
    <small class="text-danger text-center">Upload at least one before image to start</small>
    @endif
    @elseif($booking->status === 'started')
    <form action="{{ route('partner.jobs.upload-image', $booking) }}" method="POST" enctype="multipart/form-data" class="mb-2">
        @csrf
        <input type="hidden" name="image_type" value="before">
        <input type="file" name="image" accept="image/*" capture="environment" class="form-control mb-2" required>
        <button type="submit" class="btn btn-outline-secondary w-100">Quick Upload Before</button>
    </form>
    <form action="{{ route('partner.jobs.upload-image', $booking) }}" method="POST" enctype="multipart/form-data" class="mb-2">
        @csrf
        <input type="hidden" name="image_type" value="after">
        <input type="file" name="image" accept="image/*" capture="environment" class="form-control mb-2" required>
        <button type="submit" class="btn btn-outline-secondary w-100">Quick Upload After</button>
    </form>
    <form action="{{ route('partner.jobs.complete', $booking) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-success btn-lg w-100" {{ $afterImages->isEmpty() ? 'disabled' : '' }}>
            <i class="bi bi-check2-all me-2"></i>Complete Job
        </button>
    </form>
    @if($afterImages->isEmpty())
    <small class="text-danger text-center">Upload at least one after image to complete</small>
    @endif
    @elseif($booking->status === 'completed')
    <div class="alert alert-success text-center mb-0"><i class="bi bi-check-circle me-1"></i>Job completed</div>
    @endif
</div>
@endsection
