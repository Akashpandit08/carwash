<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceCityController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => ServiceCity::with('zones')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:service_cities,slug'],
            'state' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,coming_soon,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return response()->json(['success' => true, 'data' => ServiceCity::create($data)], 201);
    }

    public function show(ServiceCity $city)
    {
        return response()->json(['success' => true, 'data' => $city->load('zones')]);
    }

    public function update(Request $request, ServiceCity $city)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:service_cities,slug,'.$city->id],
            'state' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,coming_soon,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $city->update($data);

        return response()->json(['success' => true, 'data' => $city->fresh('zones')]);
    }

    public function destroy(ServiceCity $city)
    {
        $city->delete();

        return response()->json(['success' => true, 'message' => 'City deleted.']);
    }
}
