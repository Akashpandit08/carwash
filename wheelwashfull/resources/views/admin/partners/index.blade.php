@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">Partners</h2>
        </div>
        <div class="col-md-4">
            <form action="{{ route('admin.partners.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, email..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-sm btn-primary">Search</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Jobs</th>
                        <th>Completed</th>
                        <th>Avg. Rating</th>
                        <th>Join Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($partners as $partner)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px;">
                                        {{ strtoupper(substr($partner->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $partner->name }}</div>
                                        <small class="text-muted">{{ $partner->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $partner->mobile_number ?? '-' }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $partner->assignedBookings_count ?? 0 }}</span>
                            </td>
                            <td>
                                <span class="badge bg-success">{{ $partner->completed_bookings ?? 0 }}</span>
                            </td>
                            <td>
                                @php $avg = $partner->average_rating; @endphp
                                @if($avg > 0)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-star-fill me-1"></i>{{ number_format($avg, 1) }}
                                    </span>
                                @else
                                    <span class="text-muted small">No ratings</span>
                                @endif
                            </td>
                            <td>{{ $partner->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.partners.show', $partner->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No partners found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($partners->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $partners->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
