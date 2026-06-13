<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceCity;
use App\Models\ServiceZone;
use App\Services\CityScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    /**
     * Display list of services
     */
    public function index(Request $request)
    {
        $query = Service::with('category');

        if ($request->filled('service_city_id')) {
            $query->where('service_city_id', $request->service_city_id);
        }

        // Filter by category
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Search
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $services = $query->paginate(20);
        $categories = ServiceCategory::all();

        return view('admin.services.index', compact('services', 'categories'));
    }

    /**
     * Show create service form
     */
    public function create(CityScopeService $cityScope)
    {
        $categories = ServiceCategory::all();
        $cities = $this->citiesForAdmin($cityScope);
        $zones = ServiceZone::with('city')->orderBy('name')->get();
        return view('admin.services.create', compact('categories', 'cities', 'zones'));
    }

    /**
     * Store new service
     */
    public function store(Request $request, CityScopeService $cityScope)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:service_categories,id',
            'service_city_id' => 'nullable|exists:service_cities,id',
            'service_zone_id' => 'nullable|exists:service_zones,id',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'vehicle_types' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->storeOnCloudinary('services')->getSecurePath();
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['service_city_id'] = $cityScope->allowedCityId(auth()->user(), $validated['service_city_id'] ?? null);

        Service::create($validated);

        return redirect()->route('admin.services.index')->with('success', 'Service created successfully');
    }

    /**
     * Show edit service form
     */
    public function edit(Service $service, CityScopeService $cityScope)
    {
        $categories = ServiceCategory::all();
        $cities = $this->citiesForAdmin($cityScope);
        $zones = ServiceZone::with('city')->orderBy('name')->get();
        return view('admin.services.edit', compact('service', 'categories', 'cities', 'zones'));
    }

    /**
     * Update service
     */
    public function update(Request $request, Service $service, CityScopeService $cityScope)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:service_categories,id',
            'service_city_id' => 'nullable|exists:service_cities,id',
            'service_zone_id' => 'nullable|exists:service_zones,id',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'vehicle_types' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($service->image) {
                // Storage::disk('public')->delete($service->image);
            }
            $validated['image'] = $request->file('image')->storeOnCloudinary('services')->getSecurePath();
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['service_city_id'] = $cityScope->allowedCityId(auth()->user(), $validated['service_city_id'] ?? null);

        $service->update($validated);

        return redirect()->route('admin.services.index')->with('success', 'Service updated successfully');
    }

    /**
     * Delete service
     */
    public function destroy(Service $service)
    {
        if ($service->image) {
            // Storage::disk('public')->delete($service->image);
        }

        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully');
    }

    private function citiesForAdmin(CityScopeService $cityScope)
    {
        $admin = auth()->user();

        return $cityScope->isSuperAdmin($admin)
            ? ServiceCity::orderBy('sort_order')->orderBy('name')->get()
            : ServiceCity::whereKey($admin->service_city_id)->get();
    }
}
