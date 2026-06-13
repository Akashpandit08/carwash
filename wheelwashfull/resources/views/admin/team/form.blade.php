@extends('admin.layout')

@section('title', ($user->exists ? 'Edit ' : 'Add ') . Str::singular($config['title']))
@section('page_title', ($user->exists ? 'Edit ' : 'Add ') . Str::singular($config['title']))

@section('content')
@php
    $cityId = old('service_city_id', $user->service_city_id);
    $zoneId = old('service_zone_id', $user->service_zone_id);
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">{{ $user->exists ? 'Edit' : 'Add' }} {{ Str::singular($config['title']) }}</h2>
            <p class="text-muted mb-0">Create or update city-scoped team profile.</p>
        </div>
        <a href="{{ route('admin.team.index', ['type' => $type] + (request('service_city_id') ? ['service_city_id' => request('service_city_id')] : [])) }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <form method="POST" action="{{ $user->exists ? route('admin.team.update', ['type' => $type, 'user' => $user]) : route('admin.team.store', ['type' => $type]) }}">
        @csrf
        @if($user->exists) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-lg-6"><div class="card h-100"><div class="card-header">Account</div><div class="card-body">
                <div class="mb-3"><label class="form-label">Name</label><input name="name" value="{{ old('name', $user->name) }}" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Mobile</label><input name="mobile_number" value="{{ old('mobile_number', $user->mobile_number) }}" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input name="email" type="email" value="{{ old('email', $user->email) }}" class="form-control"></div>
                <div><label class="form-label">Password</label><input name="password" type="password" class="form-control"><div class="form-text">{{ $user->exists ? 'Leave blank to keep current password.' : 'Defaults to 12345678 when blank.' }}</div></div>
            </div></div></div>

            <div class="col-lg-6"><div class="card h-100"><div class="card-header">City Scope</div><div class="card-body">
                <div class="mb-3"><label class="form-label">City</label>
                    <select name="service_city_id" class="form-select" {{ auth()->user()->isSuperAdmin() ? '' : 'readonly' }}>
                        @foreach($cities as $city)<option value="{{ $city->id }}" {{ (string) $cityId === (string) $city->id ? 'selected' : '' }}>{{ $city->name }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Zone</label>
                    <select name="service_zone_id" class="form-select">
                        <option value="">No zone</option>
                        @foreach($zones as $zone)<option value="{{ $zone->id }}" {{ (string) $zoneId === (string) $zone->id ? 'selected' : '' }}>{{ $zone->name }} - {{ $zone->city?->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="form-label">Service Area</label><input name="service_area" value="{{ old('service_area', $profile->service_area) }}" class="form-control"></div>
            </div></div></div>

            <div class="col-lg-6"><div class="card h-100"><div class="card-header">Profile</div><div class="card-body">
                @if($type === 'partners')
                    <div class="mb-3"><label class="form-label">Business Name</label><input name="business_name" value="{{ old('business_name', $profile->business_name) }}" class="form-control" required></div>
                    <div><label class="form-label">Address</label><textarea name="address" class="form-control" rows="4">{{ old('address', $profile->address) }}</textarea></div>
                @elseif($type === 'workers')
                    <div><label class="form-label">Skills</label><input name="skills" value="{{ old('skills', implode(', ', $profile->skills ?? [])) }}" class="form-control" placeholder="Foam wash, interior cleaning"></div>
                @else
                    <div class="mb-3"><label class="form-label">Vehicle Type</label><input name="vehicle_type" value="{{ old('vehicle_type', $profile->vehicle_type) }}" class="form-control"></div>
                    <div><label class="form-label">License Number</label><input name="license_number" value="{{ old('license_number', $profile->license_number) }}" class="form-control"></div>
                @endif
            </div></div></div>

            <div class="col-lg-6"><div class="card h-100"><div class="card-header">Status And Location</div><div class="card-body">
                <div class="mb-3"><label class="form-label">Status</label><select name="current_status" class="form-select">@foreach($config['statuses'] as $status)<option value="{{ $status }}" {{ old('current_status', $profile->current_status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>@endforeach</select></div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Latitude</label><input name="location_lat" type="number" step="0.00000001" value="{{ old('location_lat', $profile->latitude) }}" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Longitude</label><input name="location_lng" type="number" step="0.00000001" value="{{ old('location_lng', $profile->longitude) }}" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Radius (meters)</label><input name="service_radius" type="number" value="{{ old('service_radius', $profile->service_radius ?? 5000) }}" class="form-control" step="100" min="0"></div>
                </div>
                <div class="mt-3">
                    <label class="form-label">Select on Map</label>
                    <div class="input-group mb-2">
                        <input type="text" id="map-search-input" class="form-control" placeholder="Search location (e.g. Firozabad)">
                        <button class="btn btn-outline-secondary" type="button" id="map-search-btn">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                    <div id="location-picker-map" style="height: 300px; width: 100%; border-radius: 8px; border: 1px solid #dee2e6;"></div>
                </div>
            </div></div></div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.team.index', ['type' => $type]) }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary">{{ $user->exists ? 'Update' : 'Create' }}</button>
        </div>
    </form>
</div>
@endsection

@section('extra_css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endsection

@section('extra_scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var latInput = document.querySelector('input[name="location_lat"]');
        var lngInput = document.querySelector('input[name="location_lng"]');
        var radiusInput = document.querySelector('input[name="service_radius"]');
        
        var defaultLat = latInput.value ? parseFloat(latInput.value) : 28.6139;
        var defaultLng = lngInput.value ? parseFloat(lngInput.value) : 77.2090;
        var defaultRadius = radiusInput && radiusInput.value ? parseFloat(radiusInput.value) : 5000;

        var map = L.map('location-picker-map').setView([defaultLat, defaultLng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
        
        var circle = L.circle([defaultLat, defaultLng], {
            color: 'var(--primary-color, #007bff)',
            fillColor: 'var(--primary-color, #007bff)',
            fillOpacity: 0.2,
            radius: defaultRadius
        }).addTo(map);

        function updateInputs(lat, lng) {
            latInput.value = lat.toFixed(8);
            lngInput.value = lng.toFixed(8);
        }

        marker.on('dragend', function (e) {
            var position = marker.getLatLng();
            circle.setLatLng(position);
            updateInputs(position.lat, position.lng);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            circle.setLatLng(e.latlng);
            updateInputs(e.latlng.lat, e.latlng.lng);
        });
        
        function updateMapFromInputs() {
            var lat = parseFloat(latInput.value);
            var lng = parseFloat(lngInput.value);
            if(!isNaN(lat) && !isNaN(lng)) {
                var latlng = L.latLng(lat, lng);
                marker.setLatLng(latlng);
                circle.setLatLng(latlng);
                map.setView(latlng);
            }
        }
        
        latInput.addEventListener('input', updateMapFromInputs);
        lngInput.addEventListener('input', updateMapFromInputs);
        
        if (radiusInput) {
            radiusInput.addEventListener('input', function() {
                var rad = parseFloat(this.value);
                if (!isNaN(rad) && rad > 0) {
                    circle.setRadius(rad);
                }
            });
        }

        var searchBtn = document.getElementById('map-search-btn');
        var searchInput = document.getElementById('map-search-input');

        if (searchBtn && searchInput) {
            searchBtn.addEventListener('click', function() {
                var query = searchInput.value.trim();
                if (!query) return;

                var originalText = searchBtn.innerHTML;
                searchBtn.disabled = true;
                searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Searching...';

                fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        searchBtn.disabled = false;
                        searchBtn.innerHTML = originalText;
                        if (data && data.length > 0) {
                            var result = data[0];
                            var lat = parseFloat(result.lat);
                            var lon = parseFloat(result.lon);
                            
                            var latlng = L.latLng(lat, lon);
                            map.setView(latlng, 13);
                            marker.setLatLng(latlng);
                            circle.setLatLng(latlng);
                            updateInputs(lat, lon);
                        } else {
                            alert('Location not found. Please try a different search term.');
                        }
                    })
                    .catch(err => {
                        searchBtn.disabled = false;
                        searchBtn.innerHTML = originalText;
                        alert('Search failed. Please try again later.');
                    });
            });

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchBtn.click();
                }
            });
        }
    });
</script>
@endsection
