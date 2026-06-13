@extends('admin.layout')
@section('title', 'Assign Team')
@section('page_title', 'Assign Team')
@section('content')
<div class="container-fluid">
    <h2 class="mb-1">Assign Team</h2>
    <p class="text-muted">Assign only same-city team members. Same-zone members appear first.</p>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Booking</th>
                        <th>Customer</th>
                        <th>City / Zone</th>
                        <th>Service</th>
                        <th>Date / Slot</th>
                        <th>Current</th>
                        <th style="min-width: 460px;">Assign</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($bookings as $booking)
                    @php
                        $sameCity = $team->where('service_city_id', $booking->service_city_id);
                        $sortTeam = fn ($items) => $items->sortBy(fn ($user) => (int) $user->service_zone_id === (int) $booking->service_zone_id ? 0 : 1);
                        $partners = $sortTeam($sameCity->where('role', 'partner'));
                        $workers = $sortTeam($sameCity->where('role', 'worker'));
                        $drivers = $sortTeam($sameCity->where('role', 'pickup_driver'));
                    @endphp
                    <tr>
                        <td>{{ $booking->booking_number ?? '#'.$booking->id }}<br><span class="badge bg-info">{{ str_replace('_', ' ', $booking->status) }}</span></td>
                        <td>{{ $booking->user?->name ?? '-' }}<br><small class="text-muted">{{ $booking->user?->mobile_number }}</small></td>
                        <td>{{ $booking->serviceCity?->name ?? '-' }}<br><small class="text-muted">{{ $booking->serviceZone?->name ?? 'No zone' }}</small></td>
                        <td>{{ $booking->service?->name ?? '-' }}<br><small class="text-muted">{{ $booking->wash_type ?? '-' }}</small></td>
                        <td>{{ $booking->booking_date?->format('d M Y') }}<br><small class="text-muted">{{ $booking->slot_time }}</small></td>
                        <td>
                            <small class="d-block">Partner: {{ $booking->partner?->name ?? '-' }}</small>
                            <small class="d-block">Worker: {{ $booking->worker?->name ?? '-' }}</small>
                            <small class="d-block">Pickup: {{ $booking->pickupDriver?->name ?? '-' }}</small>
                        </td>
                        <td>
                            <form action="{{ route('admin.assign-team.store', $booking) }}" method="POST">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <select name="partner_id" class="form-select form-select-sm">
                                            <option value="">Partner</option>
                                            @foreach($partners as $partner)
                                                <option value="{{ $partner->id }}" {{ $booking->partner_id === $partner->id ? 'selected' : '' }}>{{ $partner->name }}{{ $partner->service_zone_id === $booking->service_zone_id ? ' (same zone)' : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select name="worker_id" class="form-select form-select-sm">
                                            <option value="">Worker</option>
                                            @foreach($workers as $worker)
                                                <option value="{{ $worker->id }}" {{ $booking->worker_id === $worker->id ? 'selected' : '' }}>{{ $worker->name }}{{ $worker->service_zone_id === $booking->service_zone_id ? ' (same zone)' : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select name="pickup_driver_id" class="form-select form-select-sm">
                                            <option value="">Pickup driver</option>
                                            @foreach($drivers as $driver)
                                                <option value="{{ $driver->id }}" {{ $booking->pickup_driver_id === $driver->id ? 'selected' : '' }}>{{ $driver->name }}{{ $driver->service_zone_id === $booking->service_zone_id ? ' (same zone)' : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select name="delivery_driver_id" class="form-select form-select-sm">
                                            <option value="">Delivery driver</option>
                                            @foreach($drivers as $driver)
                                                <option value="{{ $driver->id }}" {{ $booking->delivery_driver_id === $driver->id ? 'selected' : '' }}>{{ $driver->name }}{{ $driver->service_zone_id === $booking->service_zone_id ? ' (same zone)' : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12"><input name="notes" class="form-control form-control-sm" placeholder="Notes"></div>
                                    <div class="col-12"><button class="btn btn-sm btn-primary">Assign Team</button></div>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No active bookings found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $bookings->appends(request()->query())->links() }}</div>
</div>
@endsection
