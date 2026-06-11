<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('category')->latest()->get();
        return response()->json(['success' => true, 'data' => $services]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $service = Service::create($validated);
        return response()->json(['success' => true, 'message' => 'Service created successfully.', 'data' => $service], 201);
    }

    public function show(Service $service)
    {
        return response()->json(['success' => true, 'data' => $service->load('category')]);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $service->update($validated);
        return response()->json(['success' => true, 'message' => 'Service updated successfully.', 'data' => $service]);
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return response()->json(['success' => true, 'message' => 'Service deleted successfully.']);
    }
}
