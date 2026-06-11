@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">Customers</h2>
        </div>
        <div class="col-md-4">
            <form action="{{ route('admin.customers.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, email, or mobile..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-sm btn-primary">Search</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Bookings</th>
                        <th>Member Since</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                    {{ $customer->name }}
                                </div>
                            </td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->mobile_number ?? '-' }}</td>
                            <td>
                                <span class="badge bg-info">{{ $customer->bookings_count ?? 0 }}</span>
                            </td>
                            <td>{{ $customer->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No customers found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($customers->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $customers->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
