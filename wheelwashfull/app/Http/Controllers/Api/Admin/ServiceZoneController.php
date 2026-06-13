<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceZone;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceZoneController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceZone::with('city')->orderBy('sort_order')->orderBy('name');

        if ($request->filled('service_city_id')) {
            $query->where('service_city_id', $request->service_city_id);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_city_id' => ['required', 'exists:service_cities,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        abort_if(
            ServiceZone::where('service_city_id', $data['service_city_id'])->where('slug', $data['slug'])->exists(),
            422,
            'The slug has already been taken for this city.'
        );

        return response()->json(['success' => true, 'data' => ServiceZone::create($data)->load('city')], 201);
    }

    public function show(ServiceZone $zone)
    {
        return response()->json(['success' => true, 'data' => $zone->load('city')]);
    }

    public function update(Request $request, ServiceZone $zone)
    {
        $data = $request->validate([
            'service_city_id' => ['required', 'exists:service_cities,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        abort_if(
            ServiceZone::where('service_city_id', $data['service_city_id'])->where('slug', $data['slug'])->whereKeyNot($zone->id)->exists(),
            422,
            'The slug has already been taken for this city.'
        );

        $zone->update($data);

        return response()->json(['success' => true, 'data' => $zone->fresh('city')]);
    }

    public function destroy(ServiceZone $zone)
    {
        $zone->delete();

        return response()->json(['success' => true, 'message' => 'Zone deleted.']);
    }
}
