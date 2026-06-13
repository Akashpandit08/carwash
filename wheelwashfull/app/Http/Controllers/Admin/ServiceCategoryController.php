<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\ServiceCity;
use App\Models\ServiceZone;
use App\Services\CityScopeService;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ServiceCategory::with(['serviceCity', 'serviceZone']);

        if ($request->filled('service_city_id')) {
            $query->where('service_city_id', $request->service_city_id);
        }

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->paginate(20);

        return view('admin.service-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(CityScopeService $cityScope)
    {
        $cities = $this->citiesForAdmin($cityScope);
        $zones = ServiceZone::with('city')->orderBy('name')->get();
        return view('admin.service-categories.create', compact('cities', 'zones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CityScopeService $cityScope)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'service_city_id' => 'nullable|exists:service_cities,id',
            'service_zone_id' => 'nullable|exists:service_zones,id',
            'icon' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon')->storeOnCloudinary('categories')->getSecurePath();
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['service_city_id'] = $cityScope->allowedCityId(auth()->user(), $validated['service_city_id'] ?? null);

        ServiceCategory::create($validated);

        return redirect()->route('admin.service-categories.index')->with('success', 'Service Category created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceCategory $serviceCategory, CityScopeService $cityScope)
    {
        $cities = $this->citiesForAdmin($cityScope);
        $zones = ServiceZone::with('city')->orderBy('name')->get();
        return view('admin.service-categories.edit', compact('serviceCategory', 'cities', 'zones'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceCategory $serviceCategory, CityScopeService $cityScope)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'service_city_id' => 'nullable|exists:service_cities,id',
            'service_zone_id' => 'nullable|exists:service_zones,id',
            'icon' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon')->storeOnCloudinary('categories')->getSecurePath();
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['service_city_id'] = $cityScope->allowedCityId(auth()->user(), $validated['service_city_id'] ?? null);

        $serviceCategory->update($validated);

        return redirect()->route('admin.service-categories.index')->with('success', 'Service Category updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceCategory $serviceCategory)
    {
        $serviceCategory->delete();

        return redirect()->route('admin.service-categories.index')->with('success', 'Service Category deleted successfully');
    }

    private function citiesForAdmin(CityScopeService $cityScope)
    {
        $admin = auth()->user();

        return $cityScope->isSuperAdmin($admin)
            ? ServiceCity::orderBy('sort_order')->orderBy('name')->get()
            : ServiceCity::whereKey($admin->service_city_id)->get();
    }
}
