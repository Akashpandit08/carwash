@php
    $adminCities = auth()->user()?->isSuperAdmin()
        ? \App\Models\ServiceCity::orderBy('sort_order')->orderBy('name')->get()
        : \App\Models\ServiceCity::where('id', auth()->user()?->service_city_id)->get();
@endphp

@if($adminCities->count())
    <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
        @foreach(request()->except('service_city_id', 'page') as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <select name="service_city_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 170px;">
            @if(auth()->user()?->isSuperAdmin())
                <option value="">All Cities</option>
            @endif
            @foreach($adminCities as $city)
                <option value="{{ $city->id }}" {{ (string) request('service_city_id') === (string) $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
            @endforeach
        </select>
    </form>
@endif
