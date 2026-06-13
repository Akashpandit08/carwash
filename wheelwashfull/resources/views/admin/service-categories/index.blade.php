@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">Service Categories</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.service-categories.create') }}" class="btn btn-primary">+ Add New Category</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.service-categories.index') }}" method="GET" class="d-flex gap-2 align-items-end">
                <div class="flex-grow-1">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name..." value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                <a href="{{ route('admin.service-categories.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>City</th>
                        <th>Zone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>
                                @if($category->icon)
                                    <img src="{{ $category->icon }}" alt="{{ $category->name }}" style="width: 44px; height: 44px; object-fit: contain; border-radius: 8px;">
                                @else
                                    <span class="text-muted small">No icon</span>
                                @endif
                            </td>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td>
                                @if($category->service_city_id)
                                    {{ $category->serviceCity->name }}
                                @else
                                    <span class="badge bg-secondary">Global</span>
                                @endif
                            </td>
                            <td>
                                @if($category->service_zone_id)
                                    {{ $category->serviceZone->name }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.service-categories.edit', $category->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('admin.service-categories.destroy', $category->id) }}')">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No categories found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        {{ $categories->links() }}
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
