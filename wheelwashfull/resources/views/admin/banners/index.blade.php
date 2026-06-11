@extends('admin.layouts.app')

@section('title', 'Banners')
@section('header-title', 'Banners')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by title">
            </div>
            <div class="col-md-3">
                <label class="form-label">User Type</label>
                <select name="user_type" class="form-select">
                    <option value="">All</option>
                    @foreach(['all', 'customer', 'partner', 'driver', 'worker'] as $type)
                        <option value="{{ $type }}" @selected(request('user_type') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill">Filter</button>
                <a href="{{ route('admin.banners.create') }}" class="btn btn-success">Add</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>User Type</th>
                    <th>Redirect</th>
                    <th>Sort</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $banner)
                    <tr>
                        <td><img src="{{ $banner->image_url }}" width="92" height="52" class="rounded object-fit-cover" alt="{{ $banner->title }}"></td>
                        <td>
                            <strong>{{ $banner->title }}</strong>
                            <div class="small text-muted">{{ $banner->subtitle }}</div>
                        </td>
                        <td><span class="badge bg-info text-dark">{{ $banner->user_type }}</span></td>
                        <td><span class="badge bg-secondary">{{ $banner->redirect_type }}</span><div class="small text-muted">{{ $banner->redirect_value }}</div></td>
                        <td>{{ $banner->sort_order }}</td>
                        <td><span class="badge bg-{{ $banner->is_active ? 'success' : 'secondary' }}">{{ $banner->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-end">
                            <form action="{{ route('admin.banners.toggle', $banner) }}" method="POST" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning">{{ $banner->is_active ? 'Inactive' : 'Active' }}</button></form>
                            <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this banner?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">No banners found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $banners->links() }}</div>
</div>
@endsection
