@extends('customer.layouts.app')

@section('title', 'Booking Details - WashMate')
@section('header-title', 'Booking Details')
@section('header-subtitle', '#' . str_pad($booking->id, 6, '0', STR_PAD_LEFT))
@section('back-url', route('customer.bookings.index'))

@section('content')
@php
    $statusColor = ['pending'=>'warning','assigned'=>'primary','accepted'=>'info','on_the_way'=>'primary','started'=>'primary','completed'=>'success','cancelled'=>'danger'][$booking->status] ?? 'secondary';
    $statusIcon  = ['pending'=>'hourglass-split','assigned'=>'person-check','accepted'=>'check-circle','on_the_way'=>'geo-alt','started'=>'arrow-repeat','completed'=>'check-circle-fill','cancelled'=>'x-circle'][$booking->status] ?? 'circle';
@endphp
<div class="mt-2">

    {{-- Status Banner --}}
    <div class="card bg-{{ $statusColor }} bg-opacity-10 border-0 mb-3">
        <div class="card-body py-3 text-center">
            <i class="bi bi-{{ $statusIcon }} text-{{ $statusColor }}" style="font-size:30px;"></i>
            <div class="fw-bold mt-1">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</div>
        </div>
    </div>

    {{-- Schedule --}}
    <div class="card">
        <div class="card-body">
            <div class="row g-0 text-center">
                <div class="col-4 border-end">
                    <div class="text-muted mb-1" style="font-size:11px;">DATE</div>
                    <div class="fw-bold" style="font-size:15px;">{{ $booking->booking_date->format('d M') }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ $booking->booking_date->format('Y') }}</div>
                </div>
                <div class="col-4 border-end">
                    <div class="text-muted mb-1" style="font-size:11px;">TIME</div>
                    <div class="fw-bold" style="font-size:15px;">{{ date('h:i', strtotime($booking->slot_time)) }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ date('A', strtotime($booking->slot_time)) }}</div>
                </div>
                <div class="col-4">
                    <div class="text-muted mb-1" style="font-size:11px;">AMOUNT</div>
                    <div class="fw-bold text-primary" style="font-size:15px;">₹{{ number_format($booking->final_price, 0) }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ strtoupper($booking->payment_method) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Service & Vehicle --}}
    <div class="card">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Service & Vehicle</h6>
            <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:46px;height:46px;">
                    <i class="bi bi-droplet-fill text-primary" style="font-size:22px;"></i>
                </div>
                <div>
                    <div class="fw-semibold">{{ $booking->service->name }}</div>
                    <div class="text-muted" style="font-size:12px;">{{ $booking->service->duration_minutes }} mins</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="bg-secondary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:46px;height:46px;">
                    <i class="bi bi-car-front-fill text-secondary" style="font-size:22px;"></i>
                </div>
                <div>
                    <div class="fw-semibold">{{ $booking->vehicle->brand }} {{ $booking->vehicle->model }}</div>
                    <div class="text-muted" style="font-size:12px;">{{ strtoupper($booking->vehicle->registration_number) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Address --}}
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-geo-alt-fill text-primary mt-1 flex-shrink-0"></i>
                <div>
                    <div class="fw-semibold mb-1" style="font-size:13px;">Service Address</div>
                    <div class="text-muted" style="font-size:13px;">{{ $booking->address }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment --}}
    <div class="card">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Payment Details</h6>
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted" style="font-size:13px;">Service Price</span>
                <span style="font-size:13px;">₹{{ number_format($booking->price, 0) }}</span>
            </div>
            @if($booking->discount > 0)
            <div class="d-flex justify-content-between py-1 text-success">
                <span style="font-size:13px;"><i class="bi bi-tag-fill me-1"></i>Discount</span>
                <span style="font-size:13px;">−₹{{ number_format($booking->discount, 0) }}</span>
            </div>
            @endif
            <div class="d-flex justify-content-between py-2 mt-1 border-top">
                <span class="fw-bold">Total</span>
                <span class="fw-bold text-primary">₹{{ number_format($booking->final_price, 0) }}</span>
            </div>
            @php
                $paymentStatusColor = match($booking->payment_status) {
                    'paid' => 'success',
                    'failed' => 'danger',
                    'refunded' => 'secondary',
                    default => 'warning',
                };
            @endphp
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted" style="font-size:13px;">Payment Method</span>
                <span style="font-size:13px;">{{ strtoupper($booking->payment_method) }}</span>
            </div>
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted" style="font-size:13px;">Payment Status</span>
                <span class="badge bg-{{ $paymentStatusColor }}">
                    {{ ucfirst($booking->payment_status) }}
                </span>
            </div>
            @if($booking->payment_method === 'online' && in_array($booking->payment_status, ['pending', 'failed']) && $booking->latestPayment)
            <div class="d-grid mt-3">
                <a href="{{ route('customer.payments.checkout', $booking->latestPayment) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-credit-card me-1"></i>{{ $booking->payment_status === 'failed' ? 'Retry Payment' : 'Complete Payment' }}
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Before / After Images --}}
    @php
        $before = $booking->images->where('image_type', 'before');
        $after  = $booking->images->where('image_type', 'after');
    @endphp
    @if($before->count() > 0 || $after->count() > 0)
    <div class="card">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Before & After</h6>
            <div class="row g-2">
                <div class="col-6">
                    <p class="text-muted mb-2 fw-semibold" style="font-size:12px;">BEFORE</p>
                    @forelse($before as $img)
                        <img src="{{ Storage::url($img->image_path) }}"
                             class="img-fluid rounded-3 mb-2 w-100"
                             style="object-fit:cover;height:130px;"
                             alt="Before" onclick="openImg(this)">
                    @empty
                        <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted"
                             style="height:130px;font-size:12px;">
                            <div class="text-center"><i class="bi bi-image d-block mb-1" style="font-size:24px;"></i>No image</div>
                        </div>
                    @endforelse
                </div>
                <div class="col-6">
                    <p class="text-muted mb-2 fw-semibold" style="font-size:12px;">AFTER</p>
                    @forelse($after as $img)
                        <img src="{{ Storage::url($img->image_path) }}"
                             class="img-fluid rounded-3 mb-2 w-100"
                             style="object-fit:cover;height:130px;"
                             alt="After" onclick="openImg(this)">
                    @empty
                        <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted"
                             style="height:130px;font-size:12px;">
                            <div class="text-center"><i class="bi bi-image d-block mb-1" style="font-size:24px;"></i>No image</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif
    @if($booking->status === 'completed')
    @if($booking->rating)
    <div class="card">
        <div class="card-body text-center">
            <h6 class="fw-bold mb-3">Your Rating</h6>
            <div class="mb-2">
                @for($i = 1; $i <= 5; $i++)
                    <i class="bi bi-star{{ $i <= $booking->rating->rating ? '-fill' : '' }} text-warning" style="font-size:26px;"></i>
                @endfor
            </div>
            @if($booking->rating->review)
            <p class="text-muted mb-0" style="font-size:14px;">"{{ $booking->rating->review }}"</p>
            @endif
        </div>
    </div>
    @else
    <div class="d-grid mb-2">
        <a href="{{ route('customer.bookings.rate', $booking) }}" class="btn btn-warning">
            <i class="bi bi-star me-2"></i>Rate This Service
        </a>
    </div>
    @endif
    @endif

    <div class="d-grid mb-3">
        <a href="{{ route('customer.bookings.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Bookings
        </a>
    </div>
</div>

{{-- Image Lightbox --}}
<div class="modal fade" id="imgModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-black border-0">
            <div class="modal-body p-1 text-center">
                <img src="" id="modal-img" class="img-fluid rounded" style="max-height:80vh;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openImg(el) {
    document.getElementById('modal-img').src = el.src;
    new bootstrap.Modal(document.getElementById('imgModal')).show();
}
</script>
@endpush
