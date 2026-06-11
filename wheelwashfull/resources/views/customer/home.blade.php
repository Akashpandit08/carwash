@extends('customer.layouts.app')

@section('title', 'Home - WashMate')
@section('header-title', 'WashMate')
@section('header-subtitle', 'Hello, ' . auth()->user()->name . ' 👋')

@section('header-action')
<a href="{{ route('customer.profile.index') }}" class="btn-back">
    <i class="bi bi-person" style="font-size:18px;"></i>
</a>
@endsection

@section('content')
<div class="mt-2">

    {{-- Search --}}
    <div class="card">
        <div class="card-body py-2 px-3">
            <div class="input-group">
                <span class="input-group-text bg-white border-0 pe-1">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" class="form-control border-0 ps-1" id="search-input"
                       placeholder="Search services..." style="box-shadow:none;">
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row g-3 mb-3">
        <div class="col-6">
            <a href="{{ route('customer.bookings.index') }}" class="card text-decoration-none" style="background:linear-gradient(135deg,#4361ee,#3f37c9);">
                <div class="card-body text-white py-3">
                    <i class="bi bi-calendar-check-fill" style="font-size:26px;"></i>
                    <div class="fw-semibold mt-1" style="font-size:13px;">My Bookings</div>
                </div>
            </a>
        </div>
        <div class="col-6">
            <a href="{{ route('customer.vehicles.create') }}" class="card text-decoration-none" style="background:linear-gradient(135deg,#06d6a0,#059b73);">
                <div class="card-body text-white py-3">
                    <i class="bi bi-plus-circle-fill" style="font-size:26px;"></i>
                    <div class="fw-semibold mt-1" style="font-size:13px;">Add Vehicle</div>
                </div>
            </a>
        </div>
    </div>

    {{-- Services by Category --}}
    <div id="services-container">
        @forelse($categories as $category)
            @if($category->services->count() > 0)
            <div class="mb-3 service-section">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0">
                        @if($category->icon)<i class="bi bi-{{ $category->icon }} me-1 text-primary"></i>@endif
                        {{ $category->name }}
                    </h6>
                    <a href="{{ route('customer.services.index') }}" class="text-primary text-decoration-none" style="font-size:12px;">
                        View All <i class="bi bi-chevron-right"></i>
                    </a>
                </div>

                @foreach($category->services as $service)
                <div class="card service-card search-item" data-name="{{ strtolower($service->name) }}"
                     onclick="window.location='{{ route('customer.services.show', $service) }}'">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:52px;height:52px;">
                                <i class="bi bi-droplet-fill text-primary" style="font-size:24px;"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <h6 class="fw-bold mb-1 text-truncate">{{ $service->name }}</h6>
                                <p class="text-muted mb-1" style="font-size:12px;line-height:1.3;">{{ Str::limit($service->description, 70) }}</p>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="text-primary fw-bold">₹{{ number_format($service->price, 0) }}</span>
                                    <span class="text-muted" style="font-size:12px;">
                                        <i class="bi bi-clock"></i> {{ $service->duration_minutes }}m
                                    </span>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        @empty
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size:60px;color:#ccc;"></i>
                <p class="text-muted mt-3">No services available</p>
            </div>
        @endforelse
    </div>

    <div id="no-results" class="text-center py-5 d-none">
        <i class="bi bi-search" style="font-size:50px;color:#ccc;"></i>
        <p class="text-muted mt-3">No services found</p>
    </div>

    {{-- Recent Bookings --}}
    @if($recentBookings->count() > 0)
    <h6 class="fw-bold mb-2 mt-2">Recent Bookings</h6>
    @foreach($recentBookings as $booking)
    @php
        $statusColor = ['pending'=>'warning','confirmed'=>'info','assigned'=>'primary','accepted'=>'info','on_the_way'=>'primary','started'=>'primary','completed'=>'success','cancelled'=>'danger'][$booking->status] ?? 'secondary';
    @endphp
    <div class="card" onclick="window.location='{{ route('customer.bookings.show', $booking) }}'">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-bold">{{ $booking->service->name }}</span>
                <span class="badge bg-{{ $statusColor }}">{{ ucfirst(str_replace('_',' ',$booking->status)) }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted" style="font-size:12px;">
                    <i class="bi bi-car-front me-1"></i>{{ $booking->vehicle->brand }} {{ $booking->vehicle->model }}
                    &nbsp;•&nbsp;<i class="bi bi-calendar me-1"></i>{{ $booking->booking_date->format('d M') }}
                </span>
                <span class="fw-bold text-primary">₹{{ number_format($booking->final_price, 0) }}</span>
            </div>
        </div>
    </div>
    @endforeach
    @endif

</div>
@endsection

@push('scripts')
<script>
document.getElementById('search-input').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    const items = document.querySelectorAll('.search-item');
    const sections = document.querySelectorAll('.service-section');
    let anyVisible = false;

    if (!q) {
        items.forEach(i => i.closest('.service-section') && (i.style.display = ''));
        sections.forEach(s => s.style.display = '');
        document.getElementById('no-results').classList.add('d-none');
        return;
    }

    sections.forEach(section => {
        let sectionVisible = false;
        section.querySelectorAll('.search-item').forEach(item => {
            const match = item.dataset.name.includes(q);
            item.style.display = match ? '' : 'none';
            if (match) { sectionVisible = true; anyVisible = true; }
        });
        section.style.display = sectionVisible ? '' : 'none';
    });

    document.getElementById('no-results').classList.toggle('d-none', anyVisible);
});
</script>
@endpush
