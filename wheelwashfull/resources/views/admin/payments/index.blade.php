@extends('admin.layout')

@section('title', 'Payments')
@section('page_title', 'Payments Management')

@section('content')
<div class="container-fluid">
    <!-- Payment Summary -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="dashboard-stat" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value">₹{{ number_format($totalRevenue, 0) }}</div>
                    </div>
                    <div class="stat-icon"><i class="bi bi-currency-rupee"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="dashboard-stat" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">COD Paid</div>
                        <div class="stat-value">₹{{ number_format($codAmount, 0) }}</div>
                    </div>
                    <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="dashboard-stat" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Online Paid</div>
                        <div class="stat-value">₹{{ number_format($onlineAmount, 0) }}</div>
                    </div>
                    <div class="stat-icon"><i class="bi bi-credit-card"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="dashboard-stat" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Pending</div>
                        <div class="stat-value">₹{{ number_format($pendingAmount, 0) }}</div>
                    </div>
                    <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Payments List</h5>
        </div>

        <form method="GET" class="p-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" 
                           placeholder="Search by booking ID or mobile..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="payment_method" class="form-select form-select-sm">
                        <option value="">All Methods</option>
                        <option value="cod" {{ request('payment_method') == 'cod' ? 'selected' : '' }}>COD</option>
                        <option value="online" {{ request('payment_method') == 'online' ? 'selected' : '' }}>Online</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="payment_status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
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
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td><small class="badge bg-secondary">{{ $payment->booking_number }}</small></td>
                            <td>
                                <small>{{ $payment->user->name }}<br>
                                <span class="text-muted">{{ $payment->user->mobile_number }}</span></small>
                            </td>
                            <td>{{ $payment->service->name }}</td>
                            <td><strong>₹{{ number_format($payment->final_price, 2) }}</strong></td>
                            <td>
                                @if($payment->payment_method == 'cod')
                                    <span class="badge bg-warning"><i class="bi bi-cash-coin"></i> COD</span>
                                @else
                                    <span class="badge bg-info"><i class="bi bi-credit-card"></i> Online</span>
                                @endif
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
                                <span class="badge bg-{{ $paymentStatusColors[$payment->payment_status] ?? 'secondary' }}">
                                    {{ ucfirst($payment->payment_status) }}
                                </span>
                            </td>
                            <td><small>{{ $payment->created_at->format('d M Y H:i') }}</small></td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No payments found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
