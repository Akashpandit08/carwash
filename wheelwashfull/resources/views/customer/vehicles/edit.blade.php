@extends('customer.layouts.app')

@section('title', 'Edit Vehicle - WashMate')
@section('header-title', 'Edit Vehicle')
@section('header-subtitle', $vehicle->brand . ' ' . $vehicle->model)
@section('back-url', route('customer.vehicles.index'))

@section('content')
<div class="mt-2">
    <div class="card">
        <div class="card-body">
            <label class="form-label fw-semibold mb-3">Vehicle Type <span class="text-danger">*</span></label>
            <div class="row g-2">
                @foreach(['car'=>['Car','car-front-fill'],'bike'=>['Bike','bicycle'],'suv'=>['SUV','car-front-fill'],'truck'=>['Truck','truck']] as $val=>[$label,$icon])
                <div class="col-3">
                    <input type="radio" class="btn-check" name="_type_pick" id="tp_{{ $val }}" value="{{ $val }}"
                           {{ old('vehicle_type', $vehicle->vehicle_type) == $val ? 'checked' : '' }}>
                    <label for="tp_{{ $val }}" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-1" style="border-radius:12px;font-size:12px;">
                        <i class="bi bi-{{ $icon }}" style="font-size:22px;"></i>{{ $label }}
                    </label>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('customer.vehicles.update', $vehicle) }}" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="vehicle_type" id="vehicle_type" value="{{ old('vehicle_type', $vehicle->vehicle_type) }}">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Brand <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('brand') is-invalid @enderror"
                           name="brand" value="{{ old('brand', $vehicle->brand) }}" required>
                    @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Model <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('model') is-invalid @enderror"
                           name="model" value="{{ old('model', $vehicle->model) }}" required>
                    @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Vehicle Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control text-uppercase @error('registration_number') is-invalid @enderror"
                           name="registration_number" value="{{ old('registration_number', $vehicle->registration_number) }}" required>
                    @error('registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Color <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="text" class="form-control @error('color') is-invalid @enderror"
                           name="color" value="{{ old('color', $vehicle->color) }}">
                    @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Update Vehicle
                    </button>
                    <a href="{{ route('customer.vehicles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('input[name="_type_pick"]').forEach(radio => {
    radio.addEventListener('change', function () {
        document.getElementById('vehicle_type').value = this.value;
    });
});
</script>
@endpush
