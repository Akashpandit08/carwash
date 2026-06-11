@extends('admin.layouts.app')

@section('title', 'Edit Banner')
@section('header-title', 'Edit Banner')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.banners._form')
        </form>
    </div>
</div>
@endsection
