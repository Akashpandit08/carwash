@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">Coupons</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">+ Add New Coupon</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.coupons.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by code or description..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-sm btn-primary">Search</button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Discount</th>
                        <th>Min Amount</th>
                        <th>Usage</th>
                        <th>Valid Until</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td>
                                <span class="badge bg-dark">{{ $coupon->code }}</span>
                            </td>
                            <td>{{ $coupon->description }}</td>
                            <td>
                                @if($coupon->discount_type === 'percentage')
                                    {{ $coupon->discount_value }}%
                                @else
                                    ₹{{ number_format($coupon->discount_value, 2) }}
                                @endif
                            </td>
                            <td>₹{{ number_format($coupon->min_order_amount, 2) }}</td>
                            <td>
                                <small>{{ $coupon->used_count ?? 0 }}/{{ $coupon->usage_limit ?? '∞' }}</small>
                            </td>
                            <td>{{ $coupon->valid_until->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $coupon->is_active ? 'success' : 'secondary' }}">
                                    {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('admin.coupons.destroy', $coupon->id) }}')">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No coupons found</td>
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
