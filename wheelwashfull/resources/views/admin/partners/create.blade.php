@extends('admin.layout')

@section('title', 'Add Partner')
@section('page_title', 'Add Partner')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Add Partner</h2>
            <p class="text-muted mb-0">Create a partner login, profile, city scope, commission, and service location.</p>
        </div>
        <a href="{{ route('admin.partners.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <form action="{{ route('admin.partners.store') }}" method="POST">
        @csrf
        @include('admin.partners._form')
    </form>
</div>
@endsection
