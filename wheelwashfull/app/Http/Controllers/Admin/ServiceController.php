<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
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
    public function create()
    {
        $categories = ServiceCategory::all();
        return view('admin.services.create', compact('categories'));
    }

    /**
     * Store new service
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:service_categories,id',
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

        Service::create($validated);

        return redirect()->route('admin.services.index')->with('success', 'Service created successfully');
    }

    /**
     * Show edit service form
     */
    public function edit(Service $service)
    {
        $categories = ServiceCategory::all();
        return view('admin.services.edit', compact('service', 'categories'));
    }

    /**
     * Update service
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:service_categories,id',
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
}
