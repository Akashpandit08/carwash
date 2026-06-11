@extends('customer.layouts.app')

@section('title', 'Add Vehicle - WashMate')
@section('header-title', 'Add Vehicle')
@section('header-subtitle', 'Enter vehicle details')
@section('back-url', route('customer.vehicles.index'))

@section('content')
<div class="mt-2">
    {{-- Vehicle Type Picker --}}
    <div class="card">
        <div class="card-body">
            <label class="form-label fw-semibold mb-3">Vehicle Type <span class="text-danger">*</span></label>
            <div class="row g-2" id="type-picker">
                @foreach(['car'=>['Car','car-front-fill'],'bike'=>['Bike','bicycle'],'suv'=>['SUV','car-front-fill'],'truck'=>['Truck','truck']] as $val=>[$label,$icon])
                <div class="col-3">
                    <input type="radio" class="btn-check" name="_type_pick" id="tp_{{ $val }}" value="{{ $val }}"
                           {{ old('vehicle_type') == $val ? 'checked' : '' }}>
                    <label for="tp_{{ $val }}" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-1" style="border-radius:12px;font-size:12px;">
                        <i class="bi bi-{{ $icon }}" style="font-size:22px;"></i>{{ $label }}
                    </label>
                </div>
                @endforeach
            </div>
            <input type="hidden" id="vehicle_type_hidden" name="" value="{{ old('vehicle_type') }}">
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('customer.vehicles.store') }}" method="POST" id="vehicle-form">
                @csrf
                <input type="hidden" name="vehicle_type" id="vehicle_type" value="{{ old('vehicle_type') }}">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Brand <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('brand') is-invalid @enderror"
                           name="brand" value="{{ old('brand') }}" placeholder="e.g. Maruti, Honda, Royal Enfield" required>
                    @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Model <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('model') is-invalid @enderror"
                           name="model" value="{{ old('model') }}" placeholder="e.g. Swift, City, Bullet" required>
                    @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="registration_number" class="form-label fw-semibold">Vehicle Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control text-uppercase @error('registration_number') is-invalid @enderror"
                           name="registration_number" value="{{ old('registration_number') }}"
                           placeholder="e.g. DL01AB1234" required>
                    @error('registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Color <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="text" class="form-control @error('color') is-invalid @enderror"
                           name="color" value="{{ old('color') }}" placeholder="e.g. White, Black, Silver">
                    @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div id="type-error" class="alert alert-danger py-2 d-none">Please select a vehicle type.</div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary" onclick="return validateType()">
                        <i class="bi bi-plus-circle me-2"></i>Add Vehicle
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
        document.getElementById('type-error').classList.add('d-none');
    });
});

function validateType() {
    if (!document.getElementById('vehicle_type').value) {
        document.getElementById('type-error').classList.remove('d-none');
        document.getElementById('type-error').scrollIntoView({ behavior: 'smooth' });
        return false;
    }
    return true;
}

// Init from old value
const oldType = '{{ old('vehicle_type') }}';
if (oldType) {
    const r = document.getElementById('tp_' + oldType);
    if (r) r.checked = true;
    document.getElementById('vehicle_type').value = oldType;
}
</script>
@endpush
