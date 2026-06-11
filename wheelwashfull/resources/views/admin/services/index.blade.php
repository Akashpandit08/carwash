@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">Services</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary">+ Add New Service</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.services.index') }}" method="GET" class="d-flex gap-2 align-items-end">
                <div class="flex-grow-1">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name..." value="{{ request('search') }}">
                </div>
                <select name="category_id" class="form-select form-select-sm" style="max-width: 200px;">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                <a href="{{ route('admin.services.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td>
                                @if($service->image_url)
                                    <img src="{{ $service->image_url }}" alt="{{ $service->name }}" style="width: 72px; height: 44px; object-fit: cover; border-radius: 8px;">
                                @else
                                    <span class="text-muted small">No image</span>
                                @endif
                            </td>
                            <td><strong>{{ $service->name }}</strong></td>
                            <td>{{ $service->category?->name ?? '-' }}</td>
                            <td>₹{{ number_format($service->price, 2) }}</td>
                            <td>{{ $service->duration_minutes }} mins</td>
                            <td>
                                <span class="badge bg-{{ $service->is_active ? 'success' : 'secondary' }}">
                                    {{ $service->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('admin.services.destroy', $service->id) }}')">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No services found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    @method('DELETE')
    @csrf
</form>

<script>
function confirmDelete(url) {
    if (confirm('Are you sure? This action cannot be undone.')) {
        document.getElementById('deleteForm').action = url;
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endsection
