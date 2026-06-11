<div class="row">
    <div class="col-lg-7">
        <div class="mb-3">
            <label class="form-label" for="title">Title</label>
            <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $banner?->title) }}" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="subtitle">Subtitle</label>
            <input class="form-control @error('subtitle') is-invalid @enderror" id="subtitle" name="subtitle" value="{{ old('subtitle', $banner?->subtitle) }}">
            @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="image">Banner Image</label>
            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*" {{ $banner ? '' : 'required' }}>
            <div class="form-text">Recommended wide image, for example 1200 x 520.</div>
            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        @if($banner?->image_url)
            <div class="mb-3">
                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" style="max-width: 320px; border-radius: 12px;">
            </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="mb-3">
            <label class="form-label" for="position">Position</label>
            <select class="form-select @error('position') is-invalid @enderror" id="position" name="position" required>
                @foreach(['home_top', 'home_middle', 'services_top', 'offers_top'] as $position)
                    <option value="{{ $position }}" {{ old('position', $banner?->position ?? 'home_top') === $position ? 'selected' : '' }}>{{ $position }}</option>
                @endforeach
            </select>
            @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="type">Redirect Type</label>
            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                @foreach(['screen', 'service', 'booking', 'external', 'none'] as $type)
                    <option value="{{ $type }}" {{ old('type', $banner?->type ?? 'screen') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="redirect_screen">Redirect Screen</label>
            <input class="form-control @error('redirect_screen') is-invalid @enderror" id="redirect_screen" name="redirect_screen" value="{{ old('redirect_screen', $banner?->redirect_screen) }}" placeholder="/services or /service-detail">
            @error('redirect_screen')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="redirect_value">Redirect Value</label>
            <input class="form-control @error('redirect_value') is-invalid @enderror" id="redirect_value" name="redirect_value" value="{{ old('redirect_value', $banner?->redirect_value) }}" placeholder="service id, booking id, or URL">
            @error('redirect_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="sort_order">Sort Order</label>
            <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $banner?->sort_order ?? 0) }}">
            @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $banner?->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>
