@extends('admin.layout')

@section('title', 'Bookings')
@section('page_title', 'Bookings Management')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Bookings List</h5>
        </div>

        <form method="GET" class="p-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <label class="form-label"><small>Date From</small></label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><small>Date To</small></label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><small>Status</small></label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="on_the_way" {{ request('status') == 'on_the_way' ? 'selected' : '' }}>On The Way</option>
                        <option value="started" {{ request('status') == 'started' ? 'selected' : '' }}>Started</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><small>Payment Status</small></label>
                    <select name="payment_status" class="form-select form-select-sm">
                        <option value="">All Payment Status</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><small>Partner</small></label>
                    <select name="partner_id" class="form-select form-select-sm">
                        <option value="">All Partners</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><small>Customer Mobile</small></label>
                    <input type="text" name="customer_mobile" class="form-control form-control-sm" value="{{ request('customer_mobile') }}">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Amount</th>
                        <th>Partner</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td><small class="badge bg-secondary">{{ $booking->booking_number }}</small></td>
                            <td>
                                <small>{{ $booking->user->name }}<br>
                                <span class="text-muted">{{ $booking->user->mobile_number }}</span></small>
                            </td>
                            <td>{{ $booking->service->name }}</td>
                            <td>{{ $booking->booking_date->format('d M Y') }}<br><small>{{ $booking->slot_time }}</small></td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'assigned' => 'primary',
                                        'accepted' => 'info',
                                        'on_the_way' => 'info',
                                        'started' => 'primary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$booking->status] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $paymentStatusColors = [
                                        'pending' => 'warning',
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        'refunded' => 'info',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $paymentStatusColors[$booking->payment_status] ?? 'secondary' }}">
                                    {{ ucfirst($booking->payment_status) }}
                                </span><br>
                                <small class="text-muted">({{ strtoupper($booking->payment_method) }})</small>
                            </td>
                            <td>₹{{ number_format($booking->final_price, 2) }}</td>
                            <td>{{ $booking->partner->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No bookings found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
