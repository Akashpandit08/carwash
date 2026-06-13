@extends('admin.layout')

@section('title', 'City Admins')
@section('page_title', 'City Admins')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h2 class="mb-0">City Admins</h2><p class="text-muted mb-0">Create city-wise admin accounts for local operations.</p></div>
        <form method="GET" class="d-flex gap-2">
            <select name="service_city_id" class="form-select form-select-sm">
                <option value="">All cities</option>
                @foreach($cities as $city)<option value="{{ $city->id }}" {{ (string) request('service_city_id') === (string) $city->id ? 'selected' : '' }}>{{ $city->name }}</option>@endforeach
            </select>
            <button class="btn btn-sm btn-primary">Filter</button>
        </form>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">Create City Admin</h6></div>
        <div class="card-body">
            <form action="{{ route('admin.city-admins.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-2"><input name="name" class="form-control" placeholder="Name" required></div>
                <div class="col-md-2"><input name="mobile_number" class="form-control" placeholder="Mobile" required></div>
                <div class="col-md-2"><input name="email" type="email" class="form-control" placeholder="Email"></div>
                <div class="col-md-2"><input name="password" type="password" class="form-control" placeholder="Password"></div>
                <div class="col-md-2">
                    <select name="service_city_id" class="form-select" required>
                        <option value="">City</option>
                        @foreach($cities as $city)<option value="{{ $city->id }}">{{ $city->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-primary w-100">Create Admin</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Name</th><th>Mobile</th><th>Email</th><th>City</th><th>Zone</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($admins as $admin)
                        <tr>
                            <form action="{{ route('admin.city-admins.update', $admin) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <td><input name="name" class="form-control form-control-sm" value="{{ $admin->name }}" required></td>
                                <td><input name="mobile_number" class="form-control form-control-sm" value="{{ $admin->mobile_number }}" required></td>
                                <td><input name="email" type="email" class="form-control form-control-sm" value="{{ $admin->email }}"></td>
                                <td>
                                    <select name="service_city_id" class="form-select form-select-sm" required>
                                        @foreach($cities as $city)<option value="{{ $city->id }}" {{ $admin->service_city_id === $city->id ? 'selected' : '' }}>{{ $city->name }}</option>@endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="service_zone_id" class="form-select form-select-sm">
                                        <option value="">No zone</option>
                                        @foreach($zones as $zone)<option value="{{ $zone->id }}" {{ $admin->service_zone_id === $zone->id ? 'selected' : '' }}>{{ $zone->name }} - {{ $zone->city?->name }}</option>@endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="active" {{ $admin->status === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $admin->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </td>
                                <td><button class="btn btn-sm btn-outline-primary">Save</button></td>
                            </form>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No city admins found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $admins->appends(request()->query())->links() }}</div>
</div>
@endsection
