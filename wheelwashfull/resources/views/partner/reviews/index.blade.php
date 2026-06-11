@extends('partner.layouts.app')

@section('title', 'My Ratings - WashMate Partner')
@section('header-title', 'My Ratings')
@section('header-subtitle', 'See what customers say about you')

@section('content')
@php
    $starColors = [5=>'#06d6a0', 4=>'#06d6a0', 3=>'#4361ee', 2=>'#ef476f', 1=>'#ef476f'];
    $maxCount   = max(array_values($ratingDistribution) ?: [1]);
@endphp

{{-- Overall Score Card --}}
<div class="card mb-3" style="background: linear-gradient(135deg, #4361ee 0%, #7209b7 100%); color: white; border: none;">
    <div class="card-body py-4 text-center">
        <div class="fw-bold mb-1" style="font-size:13px; opacity:.85; letter-spacing:1px;">OVERALL RATING</div>
        <div style="font-size:56px; font-weight:800; line-height:1;">{{ number_format($averageRating, 1) }}</div>
        <div class="mb-2">
            @for($i = 1; $i <= 5; $i++)
                <i class="bi bi-star{{ $i <= round($averageRating) ? '-fill' : ($i - 0.5 <= $averageRating ? '-half' : '') }}"
                   style="font-size:22px; color:#ffd60a;"></i>
            @endfor
        </div>
        <div style="opacity:.8; font-size:13px;">Based on {{ $totalRatings }} {{ Str::plural('review', $totalRatings) }}</div>
    </div>
</div>

{{-- Rating Distribution --}}
@if($totalRatings > 0)
<div class="card mb-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Rating Breakdown</h6>
        @for($i = 5; $i >= 1; $i--)
        @php $count = $ratingDistribution[$i] ?? 0; $pct = $maxCount > 0 ? ($count / $maxCount * 100) : 0; @endphp
        <div class="d-flex align-items-center gap-2 mb-2">
            <span style="min-width:24px; font-size:13px; font-weight:600;">{{ $i }}</span>
            <i class="bi bi-star-fill text-warning" style="font-size:12px;"></i>
            <div class="flex-grow-1 bg-light rounded-pill" style="height:10px; overflow:hidden;">
                <div class="rounded-pill" style="width:{{ $pct }}%; height:100%; background: {{ $starColors[$i] ?? '#ccc' }}; transition: width .4s;"></div>
            </div>
            <span class="text-muted" style="min-width:24px; font-size:13px; text-align:right;">{{ $count }}</span>
        </div>
        @endfor
    </div>
</div>
@endif

{{-- Reviews List --}}
<div class="mb-2 d-flex justify-content-between align-items-center">
    <h6 class="fw-bold mb-0">Customer Reviews</h6>
    <small class="text-muted">{{ $totalRatings }} total</small>
</div>

@forelse($ratings as $review)
<div class="card mb-2">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                     style="width:38px;height:38px;font-size:15px;">
                    {{ strtoupper(substr($review->user?->name ?? '?', 0, 1)) }}
                </div>
                <div>
                    <div class="fw-semibold" style="font-size:14px;">{{ $review->user?->name ?? 'Customer' }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ $review->booking?->service?->name ?? '' }}</div>
                </div>
            </div>
            <div class="text-end">
                <div>
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }} text-warning" style="font-size:13px;"></i>
                    @endfor
                </div>
                <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
            </div>
        </div>
        @if($review->review)
        <p class="mb-0 text-muted" style="font-size:13px; line-height:1.6;">
            <i class="bi bi-quote me-1 opacity-50"></i>{{ $review->review }}
        </p>
        @endif
    </div>
</div>
@empty
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-star text-muted" style="font-size:48px;"></i>
        <h6 class="mt-3 text-muted">No reviews yet</h6>
        <p class="text-muted small">Complete your first job to start receiving ratings!</p>
    </div>
</div>
@endforelse

@endsection
