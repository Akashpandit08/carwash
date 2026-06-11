@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-0">Advanced Business Reports</h2>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label mb-1">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Partner</label>
                    <select name="partner_id" class="form-select form-select-sm">
                        <option value="">All Partners</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ ($filters['partner_id'] ?? '') == $partner->id ? 'selected' : '' }}>{{ $partner->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Service</label>
                    <select name="service_id" class="form-select form-select-sm">
                        <option value="">All Services</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ ($filters['service_id'] ?? '') == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach(['pending', 'assigned', 'accepted', 'on_the_way', 'started', 'completed', 'cancelled'] as $st)
                            <option value="{{ $st }}" {{ ($filters['status'] ?? '') == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body">
                    <p class="card-text opacity-75 mb-1">Total Bookings</p>
                    <h3 class="card-title mb-0">{{ $stats['total_bookings'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body">
                    <p class="card-text opacity-75 mb-1">Total Revenue</p>
                    <h3 class="card-title mb-0">₹{{ number_format($stats['total_revenue'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white border-0 shadow-sm" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="card-body">
                    <p class="card-text opacity-75 mb-1">Online Payment Revenue</p>
                    <h3 class="card-title mb-0">₹{{ number_format($stats['online_revenue'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body">
                    <p class="card-text opacity-75 mb-1">COD Revenue</p>
                    <h3 class="card-title mb-0">₹{{ number_format($stats['cod_revenue'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Bookings by Status -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Bookings by Status</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Status</th><th>Count</th></tr>
                        </thead>
                        <tbody>
                            @forelse($statusReport as $row)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ ucfirst($row->status) }}</span></td>
                                    <td class="fw-bold">{{ $row->total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Monthly Bookings -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Monthly Bookings & Revenue</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Month/Year</th>
                                <th>Bookings</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monthlyBookings as $row)
                                <tr>
                                    <td>{{ date('F Y', mktime(0, 0, 0, $row->month, 1, $row->year)) }}</td>
                                    <td>{{ $row->total }}</td>
                                    <td class="text-success fw-bold">₹{{ number_format($row->revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Partner Performance -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Partner Performance</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Partner</th>
                                <th>Assigned</th>
                                <th>Completed</th>
                                <th>Earnings Generated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($partnerPerformance as $row)
                                <tr>
                                    <td>{{ $row->partner->name ?? 'Unknown' }}</td>
                                    <td>{{ $row->total_assignments }}</td>
                                    <td>{{ $row->completed_bookings }}</td>
                                    <td class="text-success fw-bold">₹{{ number_format($row->total_earnings, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Service Performance -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Service Performance</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Service</th>
                                <th>Total Bookings</th>
                                <th>Revenue Generated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($servicePerformance as $row)
                                <tr>
                                    <td>{{ $row->service->name ?? 'Unknown' }}</td>
                                    <td>{{ $row->total_bookings }}</td>
                                    <td class="text-success fw-bold">₹{{ number_format($row->total_revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Coupon Usage -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Coupon Usage</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Coupon Code</th>
                                <th>Usage Count</th>
                                <th>Total Discount Given</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($couponUsage as $row)
                                <tr>
                                    <td><span class="badge bg-primary">{{ $row->coupon->code ?? 'Unknown' }}</span></td>
                                    <td>{{ $row->usage_count }} times</td>
                                    <td class="text-danger fw-bold">-₹{{ number_format($row->total_discount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Daily Bookings (Recent 10) -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Recent Daily Bookings</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Bookings</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dailyBookings->take(10) as $row)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}</td>
                                    <td>{{ $row->total }}</td>
                                    <td class="text-success fw-bold">₹{{ number_format($row->revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
