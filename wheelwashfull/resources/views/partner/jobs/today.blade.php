@extends('partner.layouts.app')

@section('title', 'Today Jobs - WashMate Partner')
@section('header-title', 'Today\'s Jobs')
@section('header-subtitle', now()->format('D, d M Y'))

@section('content')
@if($jobs->isEmpty())
<div class="text-center py-5 mt-3">
    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:90px;height:90px;">
        <i class="bi bi-calendar-check text-success" style="font-size:44px;"></i>
    </div>
    <h5 class="fw-bold mb-2">No Jobs Today</h5>
    <p class="text-muted" style="font-size:14px;">You're all caught up. Check upcoming jobs.</p>
    <a href="{{ route('partner.jobs.upcoming') }}" class="btn btn-primary">View Upcoming</a>
</div>
@else
    @foreach($jobs as $job)
    @include('partner.jobs._card', ['job' => $job])
    @endforeach
@endif
@endsection
