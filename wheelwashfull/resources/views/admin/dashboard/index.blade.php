@extends('admin.layouts.app')

@section('title', 'Dashboard Overview')
@section('header-title', 'Dashboard Overview')

@section('content')
<div class="row g-4 mb-4">
    <!-- Total Bookings -->
    <div class="col-12 col-md-4 col-lg-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-white-50">Total Bookings</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['total_bookings']) }}</h2>
                    </div>
                    <i class="bi bi-calendar-check fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Today Bookings -->
    <div class="col-12 col-md-4 col-lg-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-white-50">Today's Bookings</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['today_bookings']) }}</h2>
                    </div>
                    <i class="bi bi-calendar-day fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Bookings -->
    <div class="col-12 col-md-4 col-lg-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-dark-50">Pending Bookings</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['pending_bookings']) }}</h2>
                    </div>
                    <i class="bi bi-hourglass-split fs-1 text-dark-50" style="opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Bookings -->
    <div class="col-12 col-md-4 col-lg-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-white-50">Completed</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['completed_bookings']) }}</h2>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="col-12 col-md-4 col-lg-4">
        <div class="card bg-dark text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-white-50">Total Revenue</h6>
                        <h2 class="mb-0 fw-bold">₹{{ number_format($stats['total_revenue']) }}</h2>
                    </div>
                    <i class="bi bi-currency-rupee fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- COD Amount -->
    <div class="col-12 col-md-4 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-muted">COD Amount</h6>
                        <h2 class="mb-0 fw-bold text-dark">₹{{ number_format($stats['cod_amount']) }}</h2>
                    </div>
                    <i class="bi bi-cash fs-1 text-muted"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Online Paid Amount -->
    <div class="col-12 col-md-4 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-muted">Online Paid Amount</h6>
                        <h2 class="mb-0 fw-bold text-dark">₹{{ number_format($stats['online_paid_amount']) }}</h2>
                    </div>
                    <i class="bi bi-credit-card fs-1 text-muted"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Customers -->
    <div class="col-12 col-md-6 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-muted">Total Customers</h6>
                        <h2 class="mb-0 fw-bold text-primary">{{ number_format($stats['total_customers']) }}</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-people text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Partners -->
    <div class="col-12 col-md-6 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-muted">Total Partners</h6>
                        <h2 class="mb-0 fw-bold text-info">{{ number_format($stats['total_partners']) }}</h2>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-person-badge text-info fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Bookings -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Recent Bookings</h5>
        <a href="{{ url('admin/bookings') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Booking ID</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentBookings as $booking)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $booking->booking_number ?? 'WM-'.$booking->id }}</td>
                            <td>{{ $booking->user->name ?? 'N/A' }}</td>
                            <td>{{ $booking->service->name ?? 'N/A' }}</td>
                            <td>
                                {{ $booking->booking_date->format('M d, Y') }}<br>
                                <small class="text-muted">{{ date('h:i A', strtotime($booking->slot_time)) }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $booking->status === 'completed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'info') }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No recent bookings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
