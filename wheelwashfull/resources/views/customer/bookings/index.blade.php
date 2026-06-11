@extends('customer.layouts.app')

@section('title', 'My Bookings - WashMate')
@section('header-title', 'My Bookings')
@section('header-subtitle', 'Track your services')

@section('content')
<div class="mt-2">
    {{-- Filter Tabs --}}
    <div class="d-flex gap-2 overflow-auto pb-2 mb-2" style="scrollbar-width:none;">
        @php
            $filter = request('status', 'all');
            $tabs = ['all'=>'All','pending'=>'Pending','assigned'=>'Assigned','accepted'=>'Accepted','on_the_way'=>'On The Way','started'=>'Started','completed'=>'Completed','cancelled'=>'Cancelled'];
        @endphp
        @foreach($tabs as $key => $label)
        <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}"
           class="btn btn-sm flex-shrink-0 {{ $filter === $key ? 'btn-primary' : 'btn-outline-secondary' }}"
           style="border-radius:20px;font-size:12px;white-space:nowrap;">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @if($bookings->isEmpty())
    <div class="text-center py-5 mt-3">
        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
             style="width:90px;height:90px;">
            <i class="bi bi-calendar-x text-primary" style="font-size:44px;"></i>
        </div>
        <h5 class="fw-bold mb-2">No Bookings Found</h5>
        <p class="text-muted mb-4" style="font-size:14px;">Book your first car wash service now</p>
        <a href="{{ route('customer.services.index') }}" class="btn btn-primary px-4">
            <i class="bi bi-plus-circle me-2"></i>Book a Service
        </a>
    </div>
    @else
        @foreach($bookings as $booking)
        @php
            $statusColor = ['pending'=>'warning','assigned'=>'primary','accepted'=>'info','on_the_way'=>'primary','started'=>'primary','completed'=>'success','cancelled'=>'danger'][$booking->status] ?? 'secondary';
        @endphp
        <div class="card" onclick="window.location='{{ route('customer.bookings.show', $booking) }}'">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="flex-grow-1 min-w-0 me-2">
                        <h6 class="fw-bold mb-0 text-truncate">{{ $booking->service->name }}</h6>
                        <span class="text-muted" style="font-size:11px;">#{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <span class="badge bg-{{ $statusColor }} flex-shrink-0">
                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                    </span>
                </div>

                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="text-muted" style="font-size:12px;">
                        <i class="bi bi-car-front me-1"></i>{{ $booking->vehicle->brand }} {{ $booking->vehicle->model }}
                    </span>
                    <span class="badge bg-{{ $booking->payment_method === 'cod' ? 'success' : 'primary' }} bg-opacity-10 text-{{ $booking->payment_method === 'cod' ? 'success' : 'primary' }}">
                        {{ strtoupper($booking->payment_method) }}
                    </span>
                    @php
                        $payColor = match($booking->payment_status) {
                            'paid' => 'success',
                            'failed' => 'danger',
                            'refunded' => 'secondary',
                            default => 'warning',
                        };
                    @endphp
                    <span class="badge bg-{{ $payColor }} bg-opacity-10 text-{{ $payColor }}">
                        {{ ucfirst($booking->payment_status) }}
                    </span>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="text-muted" style="font-size:12px;">
                        <i class="bi bi-calendar me-1"></i>{{ $booking->booking_date->format('d M Y') }}
                        &nbsp;•&nbsp;
                        <i class="bi bi-clock me-1"></i>{{ date('h:i A', strtotime($booking->slot_time)) }}
                    </span>
                    <span class="fw-bold text-primary">₹{{ number_format($booking->final_price, 0) }}</span>
                </div>

                @if($booking->status === 'completed' && !$booking->rating)
                <div class="mt-2">
                    <a href="{{ route('customer.bookings.rate', $booking) }}"
                       class="btn btn-warning btn-sm w-100"
                       onclick="event.stopPropagation();"
                       style="border-radius:8px;">
                        <i class="bi bi-star me-1"></i>Rate This Service
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endforeach

        <div class="mt-2 mb-3">
            {{ $bookings->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
