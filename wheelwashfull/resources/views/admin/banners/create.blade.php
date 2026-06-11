@extends('admin.layouts.app')

@section('title', 'Create Banner')
@section('header-title', 'Create Banner')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.banners._form')
        </form>
    </div>
</div>
@endsection
