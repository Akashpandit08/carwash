@extends('admin.layout')

@section('title', 'Review Details')
@section('page_title', 'Review Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Reviews
            </a>
        </div>
        <div class="col-md-6 text-end">
            <form action="{{ route('admin.reviews.destroy', $rating) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Permanently delete this review?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete Review</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Rating & Review</h6>
                    <div>
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= $rating->rating ? '-fill' : '' }} text-warning" style="font-size:18px;"></i>
                        @endfor
                        <span class="ms-2 badge bg-warning text-dark">{{ $rating->rating }}/5</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($rating->review)
                        <p class="mb-0" style="font-size:15px;line-height:1.7;">{{ $rating->review }}</p>
                    @else
                        <p class="text-muted mb-0"><em>No written review provided.</em></p>
                    @endif
                    <small class="text-muted d-block mt-3">Submitted on {{ $rating->created_at->format('d M Y \a\t h:i A') }}</small>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">Booking Information</h6></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Booking Number</label>
                            <p><strong>{{ $rating->booking->booking_number ?? '#' . $rating->booking->id }}</strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Service</label>
                            <p><strong>{{ $rating->booking->service?->name ?? '-' }}</strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Booking Date</label>
                            <p><strong>{{ $rating->booking->booking_date->format('d M Y') }} at {{ date('h:i A', strtotime($rating->booking->slot_time)) }}</strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Status</label>
                            <p><span class="badge bg-success">{{ ucfirst($rating->booking->status) }}</span></p>
                        </div>
                    </div>
                    <a href="{{ route('admin.bookings.show', $rating->booking) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>View Full Booking
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Customer</h6></div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold flex-shrink-0"
                             style="width:44px;height:44px;font-size:18px;">
                            {{ strtoupper(substr($rating->booking->user?->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $rating->booking->user?->name ?? '-' }}</h6>
                            <small class="text-muted">{{ $rating->booking->user?->mobile_number ?? '-' }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">Partner</h6></div>
                <div class="card-body">
                    @if($rating->booking->partner)
                    <div class="d-flex align-items-center">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold flex-shrink-0"
                             style="width:44px;height:44px;font-size:18px;">
                            {{ strtoupper(substr($rating->booking->partner->name, 0, 1)) }}
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $rating->booking->partner->name }}</h6>
                            <small class="text-muted">{{ $rating->booking->partner->mobile_number ?? '-' }}</small>
                        </div>
                    </div>
                    @else
                        <p class="text-muted mb-0">No partner assigned.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
