@extends('admin.layout')

@section('title', 'Partner Details')
@section('page_title', 'Partner Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <a href="{{ route('admin.partners.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Partners
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Left sidebar: partner info --}}
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body text-center pb-0">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 fw-bold"
                         style="width:70px;height:70px;font-size:28px;">
                        {{ strtoupper(substr($partner->name, 0, 1)) }}
                    </div>
                    <h5 class="mb-0">{{ $partner->name }}</h5>
                    <small class="text-muted">{{ $partner->email }}</small>

                    @if($avgRating)
                    <div class="mt-2 mb-1">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }} text-warning"></i>
                        @endfor
                    </div>
                    <div class="badge bg-warning text-dark mb-3">{{ $avgRating }} / 5 ({{ $ratings->count() }} reviews)</div>
                    @else
                    <div class="text-muted small mt-2 mb-3">No ratings yet</div>
                    @endif
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Mobile</span>
                        <strong>{{ $partner->mobile_number ?? '-' }}</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Total Jobs</span>
                        <span class="badge bg-primary">{{ $totalBookings }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Completed</span>
                        <span class="badge bg-success">{{ $completedBookings }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Total Earnings</span>
                        <strong class="text-success">₹{{ number_format($totalEarnings, 2) }}</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Joined</span>
                        <strong>{{ $partner->created_at->format('d M Y') }}</strong>
                    </div>
                </div>
            </div>

            {{-- Ratings summary --}}
            @if($ratings->count() > 0)
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Recent Reviews</h6></div>
                <div class="card-body p-0">
                    @foreach($ratings->take(5) as $review)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <small class="fw-semibold">{{ $review->user?->name ?? 'Customer' }}</small>
                            <div>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }} text-warning" style="font-size:11px;"></i>
                                @endfor
                            </div>
                        </div>
                        @if($review->review)
                            <small class="text-muted">{{ Str::limit($review->review, 60) }}</small>
                        @endif
                    </div>
                    @endforeach
                </div>
                @if($ratings->count() > 5)
                <div class="card-footer text-center">
                    <small class="text-muted">Showing 5 of {{ $ratings->count() }} reviews</small>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Right: assignment history --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Assignment History</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking #</th>
                                <th>Customer</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $booking)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.bookings.show', $booking) }}" class="text-decoration-none fw-semibold">
                                            {{ $booking->booking_number ?? '#' . $booking->id }}
                                        </a>
                                    </td>
                                    <td>{{ $booking->user?->name ?? '-' }}</td>
                                    <td>{{ $booking->service?->name ?? '-' }}</td>
                                    <td>{{ $booking->booking_date->format('d M Y') }}</td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending'   => 'warning',
                                                'assigned'  => 'primary',
                                                'accepted'  => 'info',
                                                'on_the_way'=> 'info',
                                                'started'   => 'primary',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$booking->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                        </span>
                                    </td>
                                    <td>₹{{ number_format($booking->final_price, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No assignments yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
