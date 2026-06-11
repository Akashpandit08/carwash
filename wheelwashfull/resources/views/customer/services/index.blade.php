@extends('customer.layouts.app')

@section('title', 'Services - WashMate')
@section('header-title', 'Services')
@section('header-subtitle', 'Choose a service')

@section('content')
<div class="mt-2">
    <div class="card">
        <div class="card-body py-2 px-3">
            <div class="input-group">
                <span class="input-group-text bg-white border-0 pe-1">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" class="form-control border-0 ps-1" id="search-services"
                       placeholder="Search services..." style="box-shadow:none;">
            </div>
        </div>
    </div>

    <div id="services-list">
        @forelse($categories as $category)
            @if($category->services->count() > 0)
            <div class="category-section mb-3">
                <h6 class="fw-bold mb-2 d-flex align-items-center gap-2">
                    @if($category->icon)
                        <span class="bg-primary bg-opacity-10 rounded-2 p-1 d-inline-flex">
                            <i class="bi bi-{{ $category->icon }} text-primary"></i>
                        </span>
                    @endif
                    {{ $category->name }}
                </h6>

                @foreach($category->services as $service)
                <div class="card service-card svc-item" data-name="{{ strtolower($service->name . ' ' . $category->name) }}"
                     onclick="window.location='{{ route('customer.services.show', $service) }}'">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:54px;height:54px;">
                                <i class="bi bi-droplet-fill text-primary" style="font-size:26px;"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <h6 class="fw-bold mb-1 text-truncate">{{ $service->name }}</h6>
                                <p class="text-muted mb-2" style="font-size:12px;line-height:1.4;">{{ Str::limit($service->description, 80) }}</p>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="text-primary fw-bold">₹{{ number_format($service->price, 0) }}</span>
                                    <span class="text-muted" style="font-size:12px;">
                                        <i class="bi bi-clock"></i> {{ $service->duration_minutes }} mins
                                    </span>
                                </div>
                                @if($service->vehicle_types)
                                <div class="mt-1 d-flex flex-wrap gap-1">
                                    @foreach($service->vehicle_types as $type)
                                        <span class="badge bg-light text-secondary border" style="font-size:10px;">{{ ucfirst($type) }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            <i class="bi bi-chevron-right text-muted flex-shrink-0"></i>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        @empty
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size:70px;color:#ccc;"></i>
                <h6 class="mt-3 text-muted">No services available</h6>
            </div>
        @endforelse
    </div>

    <div id="no-results" class="text-center py-5 d-none">
        <i class="bi bi-search" style="font-size:60px;color:#ccc;"></i>
        <h6 class="mt-3 text-muted">No services found</h6>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('search-services').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    const sections = document.querySelectorAll('.category-section');
    let anyVisible = false;

    if (!q) {
        sections.forEach(s => { s.style.display = ''; s.querySelectorAll('.svc-item').forEach(i => i.style.display = ''); });
        document.getElementById('no-results').classList.add('d-none');
        return;
    }

    sections.forEach(section => {
        let sectionVisible = false;
        section.querySelectorAll('.svc-item').forEach(item => {
            const match = item.dataset.name.includes(q);
            item.style.display = match ? '' : 'none';
            if (match) { sectionVisible = true; anyVisible = true; }
        });
        section.style.display = sectionVisible ? '' : 'none';
    });

    document.getElementById('no-results').classList.toggle('d-none', anyVisible);
});
</script>
@endpush
