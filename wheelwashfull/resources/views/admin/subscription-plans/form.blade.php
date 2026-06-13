@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">{{ $plan->id ? 'Edit Plan' : 'Add New Subscription Plan' }}</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-outline-secondary">← Back to Plans</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ $plan->id ? route('admin.subscription-plans.update', $plan->id) : route('admin.subscription-plans.store') }}" method="POST">
                        @csrf
                        @if($plan->id)
                            @method('PATCH')
                        @endif

                        <!-- Plan Name & Slug -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Plan Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" required value="{{ old('name', $plan->name) }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="slug" class="form-label">Slug</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $plan->slug) }}">
                                <small class="form-text text-muted">Leave blank to auto-generate from name</small>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $plan->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Scope: Global or City/Zone -->
                        <div class="mb-3">
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input @error('is_global') is-invalid @enderror" id="is_global" name="is_global" value="1" {{ old('is_global', $plan->is_global) ? 'checked' : '' }} onchange="toggleCityZoneFields()">
                                <label class="form-check-label" for="is_global">
                                    Global Plan
                                </label>
                                <small class="form-text text-muted d-block">Available across all cities</small>
                                @error('is_global')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="city-zone-fields" class="row" style="{{ old('is_global', $plan->is_global) ? 'display: none;' : '' }}">
                                <div class="col-md-6">
                                    <label for="service_city_id" class="form-label">City</label>
                                    <select class="form-select @error('service_city_id') is-invalid @enderror" id="service_city_id" name="service_city_id">
                                        <option value="">Select a city</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}" {{ old('service_city_id', $plan->service_city_id) == $city->id ? 'selected' : '' }}>
                                                {{ $city->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_city_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="service_zone_id" class="form-label">Zone (Optional)</label>
                                    <select class="form-select @error('service_zone_id') is-invalid @enderror" id="service_zone_id" name="service_zone_id">
                                        <option value="">-- Select Zone --</option>
                                        @foreach($zones as $zone)
                                            <option value="{{ $zone->id }}" {{ old('service_zone_id', $plan->service_zone_id) == $zone->id ? 'selected' : '' }}>
                                                {{ $zone->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_zone_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Pricing & Duration -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="price" class="form-label">Price (₹) *</label>
                                <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" step="0.01" required value="{{ old('price', $plan->price) }}">
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="duration_days" class="form-label">Duration (Days) *</label>
                                <input type="number" class="form-control @error('duration_days') is-invalid @enderror" id="duration_days" name="duration_days" required value="{{ old('duration_days', $plan->duration_days) }}">
                                @error('duration_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="max_washes_per_week" class="form-label">Max Washes/Week</label>
                                <input type="number" class="form-control @error('max_washes_per_week') is-invalid @enderror" id="max_washes_per_week" name="max_washes_per_week" value="{{ old('max_washes_per_week', $plan->max_washes_per_week) }}">
                                @error('max_washes_per_week')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Washes Breakdown -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="exterior_washes" class="form-label">Exterior Washes</label>
                                <input type="number" class="form-control @error('exterior_washes') is-invalid @enderror" id="exterior_washes" name="exterior_washes" value="{{ old('exterior_washes', $plan->exterior_washes) }}">
                                @error('exterior_washes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="interior_washes" class="form-label">Interior Washes</label>
                                <input type="number" class="form-control @error('interior_washes') is-invalid @enderror" id="interior_washes" name="interior_washes" value="{{ old('interior_washes', $plan->interior_washes) }}">
                                @error('interior_washes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="foam_washes" class="form-label">Foam Washes</label>
                                <input type="number" class="form-control @error('foam_washes') is-invalid @enderror" id="foam_washes" name="foam_washes" value="{{ old('foam_washes', $plan->foam_washes) }}">
                                @error('foam_washes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <!-- Features/Add-ons -->
                        <h5 class="mb-3">Included Features</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="tyre_polish_included" name="tyre_polish_included" value="1" {{ old('tyre_polish_included', $plan->tyre_polish_included) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tyre_polish_included">
                                        Tyre Polish
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="dashboard_wipe_included" name="dashboard_wipe_included" value="1" {{ old('dashboard_wipe_included', $plan->dashboard_wipe_included) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dashboard_wipe_included">
                                        Dashboard Wipe
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="vacuum_included" name="vacuum_included" value="1" {{ old('vacuum_included', $plan->vacuum_included) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="vacuum_included">
                                        Vacuum
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="priority_booking" name="priority_booking" value="1" {{ old('priority_booking', $plan->priority_booking) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="priority_booking">
                                        Priority Booking
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="pickup_drop_included" name="pickup_drop_included" value="1" {{ old('pickup_drop_included', $plan->pickup_drop_included) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pickup_drop_included">
                                        Pickup & Drop
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="doorstep_included" name="doorstep_included" value="1" {{ old('doorstep_included', $plan->doorstep_included) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="doorstep_included">
                                        Doorstep Service
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Terms & Status -->
                        <div class="mb-3">
                            <label for="terms" class="form-label">Terms & Conditions</label>
                            <textarea class="form-control @error('terms') is-invalid @enderror" id="terms" name="terms" rows="4">{{ old('terms', $plan->terms) }}</textarea>
                            @error('terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="active" {{ old('status', $plan->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $plan->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ $plan->id ? 'Update Plan' : 'Create Plan' }}</button>
                            <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCityZoneFields() {
    const isGlobal = document.getElementById('is_global').checked;
    const cityZoneFields = document.getElementById('city-zone-fields');
    cityZoneFields.style.display = isGlobal ? 'none' : 'flex';
}
</script>
@endsection
