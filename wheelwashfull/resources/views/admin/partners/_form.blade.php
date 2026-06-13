@php
    $selectedCity = old('service_city_id', $partner->service_city_id);
    $selectedZone = old('service_zone_id', $partner->service_zone_id);
    $selectedStatus = old('current_status', $profile->current_status ?? 'active');
    $selectedCommissionType = old('commission_type', $profile->commission_type ?? 'percentage');
@endphp

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">Account Details</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Partner Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $partner->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                    <input type="text" name="mobile_number" class="form-control" value="{{ old('mobile_number', $partner->mobile_number) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $partner->email) }}">
                </div>
                <div class="mb-0">
                    <label class="form-label">Password {{ $partner->exists ? '' : '(optional)' }}</label>
                    <input type="password" name="password" class="form-control" autocomplete="new-password">
                    <div class="form-text">{{ $partner->exists ? 'Leave blank to keep current password.' : 'Defaults to 12345678 when blank.' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">City And Business</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Service City</label>
                    <select name="service_city_id" class="form-select">
                        <option value="">All / Not assigned</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ (string) $selectedCity === (string) $city->id ? 'selected' : '' }}>
                                {{ $city->name }}{{ $city->state ? ', ' . $city->state : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Service Zone</label>
                    <select name="service_zone_id" class="form-select">
                        <option value="">No zone</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" data-city="{{ $zone->service_city_id }}" {{ (string) $selectedZone === (string) $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }}{{ $zone->city?->name ? ' - ' . $zone->city->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Business Name <span class="text-danger">*</span></label>
                    <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $profile->business_name ?? '') }}" required>
                </div>
                <div class="mb-0">
                    <label class="form-label">Service Area</label>
                    <input type="text" name="service_area" class="form-control" value="{{ old('service_area', $profile->service_area ?? '') }}" placeholder="Example: Agra, Dayal Bagh">
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">Location</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="4">{{ old('address', $profile->address ?? '') }}</textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="0.00000001" name="latitude" class="form-control" value="{{ old('latitude', $profile->latitude ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="0.00000001" name="longitude" class="form-control" value="{{ old('longitude', $profile->longitude ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Radius (meters)</label>
                        <input type="number" step="100" min="0" name="service_radius" class="form-control" value="{{ old('service_radius', $profile->service_radius ?? 5000) }}">
                    </div>
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
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">Operations</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="current_status" class="form-select">
                        <option value="active" {{ $selectedStatus === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $selectedStatus === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Commission Type</label>
                    <select name="commission_type" class="form-select">
                        <option value="percentage" {{ $selectedCommissionType === 'percentage' ? 'selected' : '' }}>Percentage</option>
                        <option value="fixed" {{ $selectedCommissionType === 'fixed' ? 'selected' : '' }}>Fixed</option>
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label">Commission Value</label>
                    <input type="number" step="0.01" min="0" name="commission_value" class="form-control" value="{{ old('commission_value', $profile->commission_value ?? 0) }}">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ $partner->exists ? route('admin.partners.show', $partner) : route('admin.partners.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check2 me-1"></i>{{ $partner->exists ? 'Update Partner' : 'Create Partner' }}
    </button>
</div>

@section('extra_css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endsection

@section('extra_scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var latInput = document.querySelector('input[name="latitude"]');
        var lngInput = document.querySelector('input[name="longitude"]');
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
