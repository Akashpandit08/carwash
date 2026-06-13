@extends('admin.layout')
@section('title', 'Team Management')
@section('page_title', 'Team Management')
@section('content')
<div class="container-fluid">
    <h2 class="mb-1">Team Management</h2>
    <p class="text-muted">Partners, workers, and pickup drivers by city.</p>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><a class="card text-decoration-none text-dark h-100" href="{{ route('admin.team.index', ['type' => 'partners'] + (request('service_city_id') ? ['service_city_id' => request('service_city_id')] : [])) }}"><div class="card-body"><h5>Partners</h5><p class="text-muted mb-0">Manage partner centers and commission profiles.</p></div></a></div>
        <div class="col-md-4"><a class="card text-decoration-none text-dark h-100" href="{{ route('admin.team.index', ['type' => 'workers'] + (request('service_city_id') ? ['service_city_id' => request('service_city_id')] : [])) }}"><div class="card-body"><h5>Workers</h5><p class="text-muted mb-0">Manage worker skills and availability.</p></div></a></div>
        <div class="col-md-4"><a class="card text-decoration-none text-dark h-100" href="{{ route('admin.team.index', ['type' => 'pickup-drivers'] + (request('service_city_id') ? ['service_city_id' => request('service_city_id')] : [])) }}"><div class="card-body"><h5>Pickup Drivers</h5><p class="text-muted mb-0">Manage vehicle and license profiles.</p></div></a></div>
    </div>
    <div class="card"><div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Name</th><th>Role</th><th>Mobile</th><th>City</th><th>Zone</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr><td>{{ $user->name }}</td><td>{{ ucfirst(str_replace('_', ' ', $user->role)) }}</td><td>{{ $user->mobile_number }}</td><td>{{ $user->serviceCity?->name ?? '-' }}</td><td>{{ $user->serviceZone?->name ?? '-' }}</td><td>{{ $user->status ?? 'active' }}</td></tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No team members found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div>
    <div class="mt-3">{{ $users->appends(request()->query())->links() }}</div>
</div>
@endsection
