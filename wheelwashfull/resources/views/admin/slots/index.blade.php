@extends('admin.layout')

@section('title', 'Slots')
@section('page_title', 'Time Slots Management')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Time Slots List</h5>
            <a href="{{ route('admin.slots.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Add Slot
            </a>
        </div>

        <form method="GET" class="p-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control form-control-sm" 
                           value="{{ request()->has('date') ? request('date') : now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" 
                           placeholder="Search by time..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.slots.index', ['date' => '']) }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-x-circle"></i> Show All
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Max Bookings</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slots as $slot)
                        <tr>
                            <td>{{ $slot->date->format('d M Y') }}</td>
                            <td>{{ $slot->start_time }}</td>
                            <td>{{ $slot->end_time }}</td>
                            <td>{{ $slot->max_bookings ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $slot->is_active ? 'success' : 'danger' }}">
                                    {{ $slot->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.slots.edit', $slot) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.slots.destroy', $slot) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No slots found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $slots->links() }}
        </div>
    </div>
</div>
@endsection
