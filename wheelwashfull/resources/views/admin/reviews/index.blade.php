@extends('admin.layout')

@section('title', 'Reviews')
@section('page_title', 'Reviews & Ratings')

@section('content')
<div class="container-fluid">
    <!-- Review Summary -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="dashboard-stat" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Reviews</div>
                        <div class="stat-value">{{ $totalReviews }}</div>
                    </div>
                    <div class="stat-icon"><i class="bi bi-chat-quote"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="dashboard-stat" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Average Rating</div>
                        <div class="stat-value">{{ number_format($averageRating, 1) }} ⭐</div>
                    </div>
                    <div class="stat-icon"><i class="bi bi-star-fill"></i></div>
                </div>
            </div>
        </div>

        @for($i = 5; $i >= 1; $i--)
            <div class="col-md-2 mb-3">
                <div class="dashboard-stat" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">{{ $i }} ⭐ Rating</div>
                            <div class="stat-value">{{ $ratingDistribution[$i] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Reviews List</h5>
        </div>

        <form method="GET" class="p-3">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" 
                           placeholder="Search by comment..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="rating" class="form-select form-select-sm">
                        <option value="">All Ratings</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} ⭐</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $review->user?->name ?? $review->booking?->user?->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $review->user?->mobile_number ?? '' }}</small>
                            </td>
                            <td>{{ $review->booking?->service?->name ?? '-' }}</td>
                            <td>
                                <div>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }} text-warning"></i>
                                    @endfor
                                </div>
                                <small class="fw-bold">{{ $review->rating }}/5</small>
                            </td>
                            <td><small>{{ Str::limit($review->review, 60) ?? '-' }}</small></td>
                            <td><small>{{ $review->created_at->format('d M Y') }}</small></td>
                            <td>
                                <a href="{{ route('admin.reviews.show', $review) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this review?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No reviews found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection
