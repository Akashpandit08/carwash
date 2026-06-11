@extends('partner.layouts.app')

@section('title', 'Earnings - WashMate Partner')
@section('header-title', 'Earnings')
@section('header-subtitle', 'Your completed job payouts')

@section('content')
<div class="card text-center" style="background:linear-gradient(135deg,#059669,#047857);">
    <div class="card-body py-4 text-white">
        <p class="mb-1 opacity-75" style="font-size:13px;">Total Earnings</p>
        <h1 class="fw-bold mb-0">₹{{ number_format($summary['total'], 0) }}</h1>
        <p class="mb-0 opacity-75 mt-1" style="font-size:12px;">{{ $summary['jobs_completed'] }} jobs completed</p>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="text-muted" style="font-size:11px;">TODAY</div>
                <div class="fw-bold text-success">₹{{ number_format($summary['today'], 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="text-muted" style="font-size:11px;">THIS WEEK</div>
                <div class="fw-bold">₹{{ number_format($summary['week'], 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="text-muted" style="font-size:11px;">THIS MONTH</div>
                <div class="fw-bold">₹{{ number_format($summary['month'], 0) }}</div>
            </div>
        </div>
    </div>
</div>

<h6 class="fw-bold mb-2">Recent Completed Jobs</h6>
@if($recentJobs->isEmpty())
<div class="card">
    <div class="card-body text-center text-muted py-4">No completed jobs yet</div>
</div>
@else
    @foreach($recentJobs as $job)
    <div class="card" onclick="window.location='{{ route('partner.jobs.show', $job) }}'" style="cursor:pointer;">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="fw-semibold">{{ $job->service->name }}</div>
                    <div class="text-muted" style="font-size:12px;">{{ $job->booking_date->format('d M Y') }}</div>
                </div>
                <div class="fw-bold text-success">₹{{ number_format($job->final_price, 0) }}</div>
            </div>
        </div>
    </div>
    @endforeach
@endif
@endsection
