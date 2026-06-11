@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">App Banners</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.app-banners.create') }}" class="btn btn-primary">+ Add Banner</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Position</th>
                        <th>Redirect</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banners as $banner)
                        <tr>
                            <td>
                                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" style="width: 120px; height: 56px; object-fit: cover; border-radius: 8px;">
                            </td>
                            <td>
                                <strong>{{ $banner->title }}</strong>
                                <div class="text-muted small">{{ $banner->subtitle }}</div>
                            </td>
                            <td>{{ $banner->position }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $banner->type }}</span>
                                <div class="small">{{ $banner->redirect_screen ?: '-' }} {{ $banner->redirect_value ? '#'.$banner->redirect_value : '' }}</div>
                            </td>
                            <td>{{ $banner->sort_order }}</td>
                            <td>
                                <span class="badge bg-{{ $banner->is_active ? 'success' : 'secondary' }}">
                                    {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.app-banners.edit', $banner) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('admin.app-banners.destroy', $banner) }}')">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No app banners found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($banners->hasPages())
            <div class="card-footer">{{ $banners->links() }}</div>
        @endif
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    @method('DELETE')
    @csrf
</form>

<script>
function confirmDelete(url) {
    if (confirm('Delete this banner?')) {
        document.getElementById('deleteForm').action = url;
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endsection
