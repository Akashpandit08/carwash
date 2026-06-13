@extends('admin.layout')

@section('title', 'Booking Details')
@section('page_title', 'Booking Details - ' . $booking->booking_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Booking Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><small>Booking ID</small></p>
                            <h6 class="badge bg-secondary">{{ $booking->booking_number }}</h6>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><small>Status</small></p>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'assigned' => 'primary',
                                    'accepted' => 'info',
                                    'on_the_way' => 'primary',
                                    'started' => 'primary',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$booking->status] ?? 'secondary' }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><small>Service</small></p>
                            <h6>{{ $booking->service->name }}</h6>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><small>Vehicle</small></p>
                            <h6>{{ ($booking->vehicle->brand ?? '') . ' ' . ($booking->vehicle->model ?? '-') }} ({{ $booking->vehicle->registration_number ?? '-' }})</h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><small>Booking Date & Time</small></p>
                            <h6>{{ $booking->booking_date->format('d M Y') }} at {{ date('h:i A', strtotime($booking->slot_time)) }}</h6>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><small>Address</small></p>
                            <h6>{{ $booking->address }}</h6>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><small>Notes</small></p>
                            <p>{{ $booking->notes ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Status Update Form -->
                    <hr>
                    <h6>Update Status</h6>
                    <form action="{{ route('admin.bookings.updateStatus', $booking) }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-md-8">
                            <select name="status" class="form-select form-select-sm" required>
                                @foreach($validNextStatuses as $status)
                                    <option value="{{ $status }}" {{ $booking->status == $status ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Update Status</button>
                        </div>
                        <div class="col-md-12">
                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Status note (optional)"></textarea>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><small>Payment Method</small></p>
                            <h6>{{ ucfirst($booking->payment_method) }}</h6>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><small>Payment Status</small></p>
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
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><small>Price</small></p>
                            <h6>₹{{ number_format($booking->price, 2) }}</h6>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><small>Discount</small></p>
                            <h6>-₹{{ number_format($booking->discount, 2) }}</h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><small>Final Price</small></p>
                            <h5 class="text-success">₹{{ number_format($booking->final_price, 2) }}</h5>
                        </div>
                    </div>

                    <!-- Payment Status Update Form -->
                    <hr>
                    <h6>Update Payment Status</h6>
                    <form action="{{ route('admin.bookings.updatePaymentStatus', $booking) }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-md-8">
                            <select name="payment_status" class="form-select form-select-sm" required>
                                <option value="pending" {{ $booking->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $booking->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="failed" {{ $booking->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="refunded" {{ $booking->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Update</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Before / After Images --}}
            @if($booking->images->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Job Images</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <p class="text-muted fw-semibold mb-2" style="font-size:12px;">BEFORE</p>
                            @forelse($booking->images->where('image_type', 'before') as $img)
                                <img src="{{ Storage::url($img->image_path) }}" class="img-fluid rounded mb-2 w-100" style="height:150px;object-fit:cover;" alt="Before">
                            @empty
                                <div class="text-muted small">No before images</div>
                            @endforelse
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted fw-semibold mb-2" style="font-size:12px;">AFTER</p>
                            @forelse($booking->images->where('image_type', 'after') as $img)
                                <img src="{{ Storage::url($img->image_path) }}" class="img-fluid rounded mb-2 w-100" style="height:150px;object-fit:cover;" alt="After">
                            @empty
                                <div class="text-muted small">No after images</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Customer Rating --}}
            @if($booking->rating)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Customer Rating</h5>
                </div>
                <div class="card-body text-center">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= $booking->rating->rating ? '-fill' : '' }} text-warning" style="font-size:22px;"></i>
                    @endfor
                    @if($booking->rating->review)
                        <p class="text-muted mt-2 mb-0" style="font-size:14px;">"{{ $booking->rating->review }}"</p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body text-center">
                    <img src="https://ui-avatars.com/api/?name={{ $booking->user->name }}" alt="User" class="rounded-circle mb-3" style="width: 80px;">
                    <h6>{{ $booking->user->name }}</h6>
                    <p class="text-muted">{{ $booking->user->mobile_number }}</p>
                    <p class="text-muted"><small>{{ $booking->user->email ?? '-' }}</small></p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Team Assignment</h5>
                </div>
                <div class="card-body">
                    @if($booking->wash_type === 'door_to_door' || $booking->service_mode === 'doorstep')
                        @if($booking->worker)
                            <p class="text-muted mb-1"><small>Current Worker</small></p>
                            <h6>{{ $booking->worker->name }}</h6>
                            <p class="text-muted"><small>{{ $booking->worker->mobile_number }}</small></p>
                        @else
                            <p class="text-muted">Not assigned yet</p>
                        @endif

                        @if(in_array($booking->status, ['pending', 'assigned']))
                        <form action="{{ route('admin.bookings.assignTeam', $booking) }}" method="POST" class="mt-3 border-top pt-3">
                            @csrf
                            <p class="mb-2 fw-semibold">Assign / Reassign Worker</p>
                            <select name="worker_id" class="form-select form-select-sm mb-2" required>
                                <option value="">Select Worker</option>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}" {{ $booking->worker_id == $worker->id ? 'selected' : '' }}>
                                        {{ $worker->name }}
                                    </option>
                                @endforeach
                            </select>
                            <textarea name="notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Assignment notes/reason (optional)"></textarea>
                            <button type="submit" class="btn btn-primary btn-sm w-100">Assign Worker</button>
                        </form>
                        @endif
                    @else
                        @if($booking->partner || $booking->pickupDriver || $booking->deliveryDriver)
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <p class="text-muted mb-1"><small>Pickup Driver</small></p>
                                    <h6>{{ $booking->pickupDriver->name ?? 'Not assigned' }}</h6>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <p class="text-muted mb-1"><small>Washing Partner</small></p>
                                    <h6>{{ $booking->partner->name ?? 'Not assigned' }}</h6>
                                </div>
                                <div class="col-md-12">
                                    <p class="text-muted mb-1"><small>Delivery Driver</small></p>
                                    <h6>{{ $booking->deliveryDriver->name ?? 'Not assigned' }}</h6>
                                </div>
                            </div>
                        @else
                            <p class="text-muted">Not assigned yet</p>
                        @endif

                        @if(in_array($booking->status, ['pending', 'assigned']))
                        <form action="{{ route('admin.bookings.assignTeam', $booking) }}" method="POST" class="mt-3 border-top pt-3">
                            @csrf
                            <p class="mb-2 fw-semibold">Assign / Reassign Team</p>
                            
                            <label class="small text-muted mb-1">Pickup Driver</label>
                            <select name="pickup_driver_id" class="form-select form-select-sm mb-2" required>
                                <option value="">Select Pickup Driver</option>
                                @foreach($pickupDrivers as $driver)
                                    <option value="{{ $driver->id }}" {{ $booking->pickup_driver_id == $driver->id ? 'selected' : '' }}>
                                        {{ $driver->name }}
                                    </option>
                                @endforeach
                            </select>

                            <label class="small text-muted mb-1">Washing Partner</label>
                            <select name="partner_id" class="form-select form-select-sm mb-2" required>
                                <option value="">Select Partner</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}" {{ $booking->partner_id == $partner->id ? 'selected' : '' }}>
                                        {{ $partner->name }}
                                    </option>
                                @endforeach
                            </select>

                            <label class="small text-muted mb-1">Delivery Driver</label>
                            <select name="delivery_driver_id" class="form-select form-select-sm mb-2" required>
                                <option value="">Select Delivery Driver</option>
                                @foreach($pickupDrivers as $driver)
                                    <option value="{{ $driver->id }}" {{ $booking->delivery_driver_id == $driver->id ? 'selected' : '' }}>
                                        {{ $driver->name }}
                                    </option>
                                @endforeach
                            </select>

                            <textarea name="notes" class="form-control form-control-sm mb-2 mt-2" rows="2" placeholder="Assignment notes/reason (optional)"></textarea>
                            <button type="submit" class="btn btn-primary btn-sm w-100">Assign Team</button>
                        </form>
                        @endif
                    @endif
                </div>
            </div>

            @if($booking->statusHistories && $booking->statusHistories->count() > 0)
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Status Timeline</h5>
                </div>
                <ul class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
                    @foreach($booking->statusHistories->sortByDesc('created_at') as $history)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $history->status)) }}</span>
                                <small class="text-muted d-block mt-1">
                                    By: {{ $history->changedByUser->name ?? 'System' }}
                                    ({{ ucfirst($history->changed_by_role ?? 'system') }})
                                </small>
                                @if($history->notes)
                                    <small class="text-muted d-block fst-italic">"{{ $history->notes }}"</small>
                                @endif
                            </div>
                            <span class="badge bg-light text-dark border" style="font-size:10px;">{{ $history->created_at->format('M d, H:i') }}</span>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($booking->assignments && $booking->assignments->count() > 0)
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Assignment History</h5>
                </div>
                <ul class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                    @foreach($booking->assignments as $assignment)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-0 small fw-bold">{{ $assignment->worker->name ?? $assignment->partner->name ?? 'Unknown' }}</h6>
                                <small class="text-muted d-block">By: {{ $assignment->assigner->name ?? 'System' }}</small>
                                @if($assignment->notes)
                                    <small class="text-muted d-block fst-italic">"{{ $assignment->notes }}"</small>
                                @endif
                            </div>
                            <span class="badge bg-light text-dark border" style="font-size: 10px;">{{ $assignment->assigned_at->format('M d, H:i') }}</span>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
