@extends('partner.layouts.app')

@section('title', 'Upcoming Jobs - WashMate Partner')
@section('header-title', 'Upcoming Jobs')
@section('header-subtitle', 'Scheduled ahead')

@section('content')
@if($jobs->isEmpty())
<div class="text-center py-5 mt-3">
    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:90px;height:90px;">
        <i class="bi bi-calendar-x text-success" style="font-size:44px;"></i>
    </div>
    <h5 class="fw-bold mb-2">No Upcoming Jobs</h5>
    <p class="text-muted" style="font-size:14px;">New assignments will appear here.</p>
</div>
@else
    @foreach($jobs as $job)
    @include('partner.jobs._card', ['job' => $job])
    @endforeach
@endif
@endsection
