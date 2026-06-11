@extends('admin.layout')

@section('title', 'Edit Slot')
@section('page_title', 'Edit Time Slot')

@section('content')
<div class="container-fluid">
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <h5 class="mb-0">Edit Time Slot</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.slots.update', $slot) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="date" class="form-label">Date *</label>
                    <input type="date" class="form-control @error('date') is-invalid @enderror" 
                           id="date" name="date" value="{{ old('date', $slot->date->format('Y-m-d')) }}" required>
                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="start_time" class="form-label">Start Time *</label>
                    <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                           id="start_time" name="start_time" value="{{ old('start_time', $slot->start_time) }}" required>
                    @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="end_time" class="form-label">End Time *</label>
                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                           id="end_time" name="end_time" value="{{ old('end_time', $slot->end_time) }}" required>
                    @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="max_bookings" class="form-label">Max Bookings</label>
                    <input type="number" class="form-control @error('max_bookings') is-invalid @enderror" 
                           id="max_bookings" name="max_bookings" value="{{ old('max_bookings', $slot->max_bookings) }}" min="1">
                    @error('max_bookings')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                           {{ old('is_active', $slot->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Update Slot
                    </button>
                    <a href="{{ route('admin.slots.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
