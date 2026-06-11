@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">Add App Banner</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.app-banners.index') }}" class="btn btn-outline-secondary">Back to Banners</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.app-banners.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('admin.app-banners.partials.form', ['banner' => null])
                <button type="submit" class="btn btn-primary">Create Banner</button>
                <a href="{{ route('admin.app-banners.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
