@php
    $statusColors = [
        'assigned' => 'warning',
        'accepted' => 'info',
        'on_the_way' => 'primary',
        'started' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];
    $color = $statusColors[$job->status] ?? 'secondary';
    $payColor = match($job->payment_status) {
        'paid' => 'success',
        'failed' => 'danger',
        'refunded' => 'secondary',
        default => 'warning',
    };
@endphp
<div class="card job-card" onclick="window.location='{{ route('partner.jobs.show', $job) }}'">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <h6 class="fw-bold mb-0">{{ $job->service->name }}</h6>
                <span class="text-muted" style="font-size:11px;">{{ $job->booking_number }}</span>
            </div>
            <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span>
        </div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-{{ $job->payment_method === 'cod' ? 'success' : 'primary' }} bg-opacity-10 text-{{ $job->payment_method === 'cod' ? 'success' : 'primary' }}">
                {{ strtoupper($job->payment_method) }}
            </span>
            <span class="badge bg-{{ $payColor }} bg-opacity-10 text-{{ $payColor }}">
                {{ ucfirst($job->payment_status) }}
            </span>
        </div>
        <div class="d-flex align-items-center gap-2 mb-2 text-muted" style="font-size:12px;">
            <span><i class="bi bi-calendar me-1"></i>{{ $job->booking_date->format('d M Y') }}</span>
            <span><i class="bi bi-clock me-1"></i>{{ date('h:i A', strtotime($job->slot_time)) }}</span>
        </div>
        <div class="d-flex align-items-start gap-2 mb-2">
            <i class="bi bi-geo-alt text-success mt-1"></i>
            <span class="text-muted" style="font-size:12px;">{{ \Illuminate\Support\Str::limit($job->address, 60) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
            <span class="text-muted" style="font-size:12px;">
                <i class="bi bi-car-front me-1"></i>{{ $job->vehicle->brand }} {{ $job->vehicle->model }}
            </span>
            <span class="fw-bold text-success">₹{{ number_format($job->final_price, 0) }}</span>
        </div>
    </div>
</div>
