@extends('admin.layout')

@section('title', 'Edit Partner')
@section('page_title', 'Edit Partner')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Edit Partner</h2>
            <p class="text-muted mb-0">{{ $partner->name }} - {{ $profile->business_name ?? 'Partner profile' }}</p>
        </div>
        <a href="{{ route('admin.partners.show', $partner) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <form action="{{ route('admin.partners.update', $partner) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.partners._form')
    </form>
</div>
@endsection
