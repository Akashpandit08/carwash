@extends('admin.layout')
@section('title', 'Settings')
@section('page_title', 'Settings')
@section('content')
<div class="container-fluid">
    <h2 class="mb-1">Settings</h2>
    <p class="text-muted">Operational settings entry point for super admin and city admin configuration.</p>
    <div class="row g-3">
        @if(auth()->user()->isSuperAdmin())
            <div class="col-md-4"><a href="{{ route('admin.cities.index') }}" class="card text-decoration-none text-dark"><div class="card-body"><h5>Cities</h5><p class="text-muted mb-0">Manage city scope.</p></div></a></div>
            <div class="col-md-4"><a href="{{ route('admin.zones.index') }}" class="card text-decoration-none text-dark"><div class="card-body"><h5>Zones</h5><p class="text-muted mb-0">Manage zone scope.</p></div></a></div>
            <div class="col-md-4"><a href="{{ route('admin.city-admins.index') }}" class="card text-decoration-none text-dark"><div class="card-body"><h5>City Admins</h5><p class="text-muted mb-0">Create local admins.</p></div></a></div>
        @else
            <div class="col-md-6"><div class="card"><div class="card-body"><h5>City Scope</h5><p class="text-muted mb-0">Your admin account is scoped to {{ auth()->user()->serviceCity?->name ?? 'your assigned city' }}.</p></div></div></div>
        @endif
    </div>
</div>
@endsection
