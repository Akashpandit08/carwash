@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-5">
            <h2 class="mb-0">Partners</h2>
            <p class="text-muted mb-0">Manage partner accounts, service cities, location, status, and commission.</p>
        </div>
        <div class="col-md-7">
            <form action="{{ route('admin.partners.index') }}" method="GET" class="d-flex flex-wrap justify-content-md-end gap-2">
                <input type="text" name="search" class="form-control form-control-sm" style="max-width: 220px;" placeholder="Search name, mobile, business..." value="{{ request('search') }}">
                <select name="service_city_id" class="form-select form-select-sm" style="max-width: 170px;">
                    <option value="">All cities</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" {{ (string) request('service_city_id') === (string) $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select form-select-sm" style="max-width: 140px;">
                    <option value="">All status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Search</button>
                <a href="{{ route('admin.partners.create') }}" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-lg me-1"></i>Add Partner
                </a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Business</th>
                        <th>City / Zone</th>
                        <th>Mobile</th>
                        <th>Status</th>
                        <th>Commission</th>
                        <th>Jobs</th>
                        <th>Completed</th>
                        <th>Avg. Rating</th>
                        <th>Join Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($partners as $partner)
                        @php
                            $profile = $partner->partnerProfile;
                            $status = $profile->current_status ?? 'inactive';
                        @endphp
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
                            <td>
                                <div class="fw-semibold">{{ $profile->business_name ?? '-' }}</div>
                                <small class="text-muted">{{ $profile->service_area ?? 'No service area' }}</small>
                            </td>
                            <td>
                                <div>{{ $partner->serviceCity?->name ?? '-' }}</div>
                                <small class="text-muted">{{ $partner->serviceZone?->name ?? 'No zone' }}</small>
                            </td>
                            <td>{{ $partner->mobile_number ?? '-' }}</td>
                            <td>
                                <form action="{{ route('admin.partners.toggleStatus', $partner) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm badge bg-{{ $status === 'active' ? 'success' : 'secondary' }} border-0">
                                        {{ ucfirst($status) }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                @if($profile)
                                    {{ ucfirst($profile->commission_type ?? 'percentage') }}:
                                    {{ rtrim(rtrim(number_format((float) $profile->commission_value, 2), '0'), '.') }}{{ ($profile->commission_type ?? 'percentage') === 'percentage' ? '%' : '' }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
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
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.partners.show', $partner) }}" class="btn btn-outline-primary">View</a>
                                    <a href="{{ route('admin.partners.edit', $partner) }}" class="btn btn-outline-secondary">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">No partners found</td>
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
