@php
    $redirects = ['home', 'services', 'service_detail', 'booking', 'booking_detail', 'offers', 'profile', 'external_url', 'custom_screen'];
    $userTypes = ['all', 'customer', 'partner', 'driver', 'worker'];
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Title</label>
        <input name="title" value="{{ old('title', $banner->title) }}" class="form-control @error('title') is-invalid @enderror" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Subtitle</label>
        <input name="subtitle" value="{{ old('subtitle', $banner->subtitle) }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">Image</label>
        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*" {{ $banner->exists ? '' : 'required' }} onchange="previewImage(event)">
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <img id="image-preview" src="{{ $banner->image_url }}" class="mt-3 rounded {{ $banner->image ? '' : 'd-none' }}" style="max-width: 260px; max-height: 140px; object-fit: cover;">
    </div>
    <div class="col-md-3">
        <label class="form-label">Target User Type</label>
        <select name="user_type" class="form-select" required>
            @foreach($userTypes as $type)
                <option value="{{ $type }}" @selected(old('user_type', $banner->user_type ?: 'all') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Redirect Type</label>
        <select name="redirect_type" id="redirect_type" class="form-select" required onchange="updateRedirectHelp()">
            @foreach($redirects as $redirect)
                <option value="{{ $redirect }}" @selected(old('redirect_type', $banner->redirect_type ?: 'home') === $redirect)>{{ ucfirst(str_replace('_', ' ', $redirect)) }}</option>
            @endforeach
        </select>
        <div id="redirect-help" class="form-text"></div>
    </div>
    <div class="col-md-8">
        <label class="form-label">Redirect Value</label>
        <input name="redirect_value" value="{{ old('redirect_value', $banner->redirect_value) }}" class="form-control" placeholder="Optional ID, URL, or app route">
    </div>
    <div class="col-md-4">
        <label class="form-label">Start Date</label>
        <input type="datetime-local" name="start_date" value="{{ old('start_date', optional($banner->start_date)->format('Y-m-d\TH:i')) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">End Date</label>
        <input type="datetime-local" name="end_date" value="{{ old('end_date', optional($banner->end_date)->format('Y-m-d\TH:i')) }}" class="form-control">
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $banner->is_active ?? true))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary">Save Banner</button>
    <a href="{{ route('admin.banners.index') }}" class="btn btn-light">Cancel</a>
</div>

@push('scripts')
<script>
function previewImage(event) {
    const preview = document.getElementById('image-preview');
    preview.src = URL.createObjectURL(event.target.files[0]);
    preview.classList.remove('d-none');
}
function updateRedirectHelp() {
    const help = {
        service_detail: 'Enter service ID.',
        booking_detail: 'Enter booking ID.',
        external_url: 'Enter full URL.',
        custom_screen: 'Enter app route name.'
    };
    const value = document.getElementById('redirect_type').value;
    document.getElementById('redirect-help').textContent = help[value] || 'No redirect value needed unless this target requires one.';
}
updateRedirectHelp();
</script>
@endpush
