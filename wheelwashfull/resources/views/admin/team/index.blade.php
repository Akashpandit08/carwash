@extends('admin.layout')

@section('title', $config['title'])
@section('page_title', $config['title'])

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="mb-0">{{ $config['title'] }}</h2>
            <p class="text-muted mb-0">City-scoped {{ strtolower($config['title']) }} management.</p>
        </div>
        <a href="{{ route('admin.team.create', ['type' => $type] + (request('service_city_id') ? ['service_city_id' => request('service_city_id')] : [])) }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>Add {{ Str::singular($config['title']) }}
        </a>
    </div>

    <form method="GET" class="card card-body mb-3">
        <div class="row g-2">
            <div class="col-md-4"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, mobile, email"></div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All status</option>
                    @foreach($config['statuses'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="service_city_id" class="form-select">
                    @if(auth()->user()->isSuperAdmin())<option value="">All cities</option>@endif
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" {{ (string) request('service_city_id') === (string) $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>City / Zone</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $teamUser)
                        @php($profile = $teamUser->{$config['relation']})
                        <tr>
                            <td><div class="fw-semibold">{{ $teamUser->name }}</div><small class="text-muted">{{ $teamUser->email }}</small></td>
                            <td>{{ $teamUser->mobile_number }}</td>
                            <td><div>{{ $teamUser->serviceCity?->name ?? '-' }}</div><small class="text-muted">{{ $teamUser->serviceZone?->name ?? 'No zone' }}</small></td>
                            <td>
                                @if($type === 'partners')
                                    <div>{{ $profile->business_name ?? '-' }}</div>
                                @elseif($type === 'workers')
                                    <div>{{ implode(', ', $profile->skills ?? []) ?: '-' }}</div>
                                @else
                                    <div>{{ $profile->vehicle_type ?? '-' }}</div><small class="text-muted">{{ $profile->license_number ?? '' }}</small>
                                @endif
                                <small class="text-muted">{{ $profile->service_area ?? 'No service area' }}</small>
                            </td>
                            <td><span class="badge bg-{{ ($profile->current_status ?? '') === 'inactive' ? 'secondary' : 'success' }}">{{ ucfirst($profile->current_status ?? '-') }}</span></td>
                            <td>
                                @if($profile?->latitude && $profile?->longitude)
                                    {{ $profile->latitude }}, {{ $profile->longitude }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.team.edit', ['type' => $type, 'user' => $teamUser] + (request('service_city_id') ? ['service_city_id' => request('service_city_id')] : [])) }}" class="btn btn-outline-primary">Edit</a>
                                    <form action="{{ route('admin.team.toggle', ['type' => $type, 'user' => $teamUser]) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-outline-secondary">{{ ($profile->current_status ?? '') === 'inactive' ? 'Activate' : 'Deactivate' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $users->appends(request()->query())->links() }}</div>
</div>
@endsection
