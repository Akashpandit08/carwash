@extends('admin.layout')

@section('title', 'Cities')
@section('page_title', 'Cities')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Cities</h2>
            <p class="text-muted mb-0">Super admin city scope for services, team, bookings, and subscriptions.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">Add City</h6></div>
        <div class="card-body">
            <form action="{{ route('admin.cities.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-3"><input name="name" class="form-control" placeholder="City name" required></div>
                <div class="col-md-2"><input name="slug" class="form-control" placeholder="Slug"></div>
                <div class="col-md-2"><input name="state" class="form-control" placeholder="State"></div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="coming_soon">Coming soon</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-1"><input name="sort_order" type="number" min="0" class="form-control" placeholder="Sort"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100">Add City</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>Name</th><th>Slug</th><th>State</th><th>Status</th><th>Zones</th><th>Sort</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($cities as $city)
                        <tr>
                            <form action="{{ route('admin.cities.update', $city) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <td><input name="name" class="form-control form-control-sm" value="{{ $city->name }}" required></td>
                                <td><input name="slug" class="form-control form-control-sm" value="{{ $city->slug }}"></td>
                                <td><input name="state" class="form-control form-control-sm" value="{{ $city->state }}"></td>
                                <td>
                                    <select name="status" class="form-select form-select-sm">
                                        @foreach(['active', 'coming_soon', 'inactive'] as $status)
                                            <option value="{{ $status }}" {{ $city->status === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><span class="badge bg-primary">{{ $city->zones_count }}</span></td>
                                <td><input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="{{ $city->sort_order }}"></td>
                                <td><button class="btn btn-sm btn-outline-primary">Save</button></td>
                            </form>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No cities found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $cities->links() }}</div>
</div>
@endsection
