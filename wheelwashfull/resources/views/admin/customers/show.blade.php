@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">Customer Details</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">← Back to Customers</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px; font-size: 24px;">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                    <h5 class="card-title">{{ $customer->name }}</h5>
                    <p class="text-muted">{{ $customer->email }}</p>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between">
                        <span>Total Bookings:</span>
                        <strong>{{ $customer->bookings_count ?? 0 }}</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span>Mobile:</span>
                        <strong>{{ $customer->mobile_number ?? '-' }}</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span>Member Since:</span>
                        <strong>{{ $customer->created_at->format('M d, Y') }}</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span>Total Spent:</span>
                        <strong>₹{{ number_format($totalSpent ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Booking History</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking ID</th>
                                <th>Service</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->bookings as $booking)
                                <tr>
                                    <td>#{{ $booking->id }}</td>
                                    <td>{{ $booking->service?->name ?? '-' }}</td>
                                    <td>{{ $booking->booking_date->format('M d, Y h:i A') }}</td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$booking->status] ?? 'secondary' }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td>₹{{ number_format($booking->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No bookings yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
