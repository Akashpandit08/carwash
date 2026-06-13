@extends('admin.layout')

@section('title', 'Zones')
@section('page_title', 'Zones')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h2 class="mb-0">Zones</h2><p class="text-muted mb-0">Manage zones inside service cities.</p></div>
        <form method="GET" class="d-flex gap-2">
            <select name="service_city_id" class="form-select form-select-sm">
                <option value="">All cities</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ (string) request('service_city_id') === (string) $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-primary">Filter</button>
        </form>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">Add Zone</h6></div>
        <div class="card-body">
            <form action="{{ route('admin.zones.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <select name="service_city_id" class="form-select" required>
                        <option value="">Select city</option>
                        @foreach($cities as $city)<option value="{{ $city->id }}">{{ $city->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3"><input name="name" class="form-control" placeholder="Zone name" required></div>
                <div class="col-md-2"><input name="slug" class="form-control" placeholder="Slug"></div>
                <div class="col-md-2"><input name="sort_order" type="number" min="0" class="form-control" placeholder="Sort"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100">Add Zone</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>City</th><th>Name</th><th>Slug</th><th>Status</th><th>Sort</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($zones as $zone)
                        <tr>
                            <form action="{{ route('admin.zones.update', $zone) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <td>
                                    <select name="service_city_id" class="form-select form-select-sm">
                                        @foreach($cities as $city)<option value="{{ $city->id }}" {{ $zone->service_city_id === $city->id ? 'selected' : '' }}>{{ $city->name }}</option>@endforeach
                                    </select>
                                </td>
                                <td><input name="name" class="form-control form-control-sm" value="{{ $zone->name }}" required></td>
                                <td><input name="slug" class="form-control form-control-sm" value="{{ $zone->slug }}"></td>
                                <td>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="active" {{ $zone->status === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $zone->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </td>
                                <td><input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="{{ $zone->sort_order }}"></td>
                                <td><button class="btn btn-sm btn-outline-primary">Save</button></td>
                            </form>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No zones found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $zones->appends(request()->query())->links() }}</div>
</div>
@endsection
