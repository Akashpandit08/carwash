<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppBanner;
use App\Services\CityScopeService;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(CityScopeService $cityScope)
    {
        $query = AppBanner::query();
        $cityScope->apply($query, auth()->user());

        $banners = $query->orderBy('sort_order')->get();
        return response()->json(['success' => true, 'data' => $banners]);
    }

    public function store(Request $request, CityScopeService $cityScope)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'service_city_id' => 'nullable|exists:service_cities,id',
            'service_zone_id' => 'nullable|exists:service_zones,id',
            'image_url' => 'required|string|max:255',
            'target_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $validated['service_city_id'] = $cityScope->allowedCityId(auth()->user(), $request->service_city_id);
        $validated['service_zone_id'] = $request->service_zone_id;

        $banner = AppBanner::create($validated);
        return response()->json(['success' => true, 'message' => 'Banner created.', 'data' => $banner], 201);
    }

    public function show(AppBanner $banner, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $banner);

        return response()->json(['success' => true, 'data' => $banner]);
    }

    public function update(Request $request, AppBanner $banner, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $banner);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'service_city_id' => 'nullable|exists:service_cities,id',
            'service_zone_id' => 'nullable|exists:service_zones,id',
            'image_url' => 'required|string|max:255',
            'target_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $validated['service_city_id'] = $cityScope->allowedCityId(auth()->user(), $request->service_city_id);
        $validated['service_zone_id'] = $request->service_zone_id;

        $banner->update($validated);
        return response()->json(['success' => true, 'message' => 'Banner updated.', 'data' => $banner]);
    }

    public function destroy(AppBanner $banner, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $banner);

        $banner->delete();
        return response()->json(['success' => true, 'message' => 'Banner deleted.']);
    }
}
