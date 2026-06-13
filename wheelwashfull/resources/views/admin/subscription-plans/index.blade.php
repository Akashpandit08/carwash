@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">Subscription Plans</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.subscription-plans.create') }}" class="btn btn-primary">+ Add New Plan</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.subscription-plans.index') }}" method="GET" class="row g-2 align-items-end">
                @if(auth()->user()->isAdmin())
                    <div class="col-md-3">
                        <label for="service_city_id" class="form-label">City</label>
                        <select name="service_city_id" id="service_city_id" class="form-select form-select-sm">
                            <option value="">All Cities</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ request('service_city_id') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                
                <div class="col-md-3">
                    <label for="service_zone_id" class="form-label">Zone</label>
                    <select name="service_zone_id" id="service_zone_id" class="form-select form-select-sm">
                        <option value="">All Zones</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" {{ request('service_zone_id') == $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }} ({{ $zone->city?->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                </div>

                @if(request()->filled('service_city_id') || request()->filled('service_zone_id') || request()->filled('status'))
                    <div class="col-md-12">
                        <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>City / Zone</th>
                        <th>Price (₹)</th>
                        <th>Duration</th>
                        <th>Total Washes</th>
                        <th>Features</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td>
                                <strong>{{ $plan->name }}</strong>
                                @if($plan->is_global)
                                    <span class="badge bg-info ms-2">Global</span>
                                @endif
                            </td>
                            <td>
                                {{ $plan->serviceCity?->name ?? '-' }}
                                @if($plan->serviceZone)
                                    <br><small class="text-muted">Zone: {{ $plan->serviceZone->name }}</small>
                                @endif
                            </td>
                            <td>₹{{ number_format($plan->price, 2) }}</td>
                            <td>{{ $plan->duration_days }} days</td>
                            <td>
                                <span title="Exterior: {{ $plan->exterior_washes }}, Interior: {{ $plan->interior_washes }}, Foam: {{ $plan->foam_washes }}">
                                    {{ $plan->total_washes }}
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem;">
                                    @if($plan->doorstep_included)
                                        <span class="badge bg-success">Doorstep</span>
                                    @endif
                                    @if($plan->pickup_drop_included)
                                        <span class="badge bg-success">Pickup/Drop</span>
                                    @endif
                                    @if($plan->priority_booking)
                                        <span class="badge bg-success">Priority</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $plan->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($plan->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.subscription-plans.edit', $plan->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                <form action="{{ route('admin.subscription-plans.toggle', $plan->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-info">
                                        {{ $plan->status === 'active' ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No subscription plans found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($plans->hasPages())
            <div class="card-footer">
                {{ $plans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
