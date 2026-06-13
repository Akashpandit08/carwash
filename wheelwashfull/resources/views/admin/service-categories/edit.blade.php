@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">Edit Category</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.service-categories.index') }}" class="btn btn-outline-secondary">← Back to Categories</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.service-categories.update', $serviceCategory->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" required value="{{ old('name', $serviceCategory->name) }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $serviceCategory->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="service_city_id" class="form-label">City</label>
                                <select class="form-select @error('service_city_id') is-invalid @enderror" id="service_city_id" name="service_city_id">
                                    <option value="">All Cities (Global)</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ old('service_city_id', $serviceCategory->service_city_id) == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('service_city_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="service_zone_id" class="form-label">Zone</label>
                                <select class="form-select @error('service_zone_id') is-invalid @enderror" id="service_zone_id" name="service_zone_id">
                                    <option value="">All Zones</option>
                                    @foreach($zones as $zone)
                                        <option value="{{ $zone->id }}" {{ old('service_zone_id', $serviceCategory->service_zone_id) == $zone->id ? 'selected' : '' }}>
                                            {{ $zone->name }} ({{ $zone->city->name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('service_zone_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="icon" class="form-label">Category Icon</label>
                            <input type="file" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" accept="image/*">
                            <div class="form-text">Upload a new icon only if you want to replace the current one.</div>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($serviceCategory->icon)
                            <div class="mb-3">
                                <img src="{{ $serviceCategory->icon }}" alt="{{ $serviceCategory->name }}" style="max-width: 100px; border-radius: 8px;">
                            </div>
                        @endif

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $serviceCategory->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Category</button>
                            <a href="{{ route('admin.service-categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
