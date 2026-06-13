<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCity;
use App\Models\ServiceZone;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class ServiceZoneController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceZone::with('city')->orderBy('sort_order')->orderBy('name');

        if ($request->filled('service_city_id')) {
            $query->where('service_city_id', $request->service_city_id);
        }

        return view('admin.zones.index', [
            'zones' => $query->paginate(20),
            'cities' => ServiceCity::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        ServiceZone::create($this->validated($request));

        return back()->with('success', 'Zone created successfully.');
    }

    public function update(Request $request, ServiceZone $zone)
    {
        $zone->update($this->validated($request, $zone));

        return back()->with('success', 'Zone updated successfully.');
    }

    public function destroy(ServiceZone $zone)
    {
        $zone->delete();

        return back()->with('success', 'Zone deleted successfully.');
    }

    private function validated(Request $request, ?ServiceZone $zone = null): array
    {
        $data = $request->validate([
            'service_city_id' => ['required', 'exists:service_cities,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $duplicate = ServiceZone::where('service_city_id', $data['service_city_id'])
            ->where('slug', $data['slug'])
            ->when($zone, fn ($query) => $query->whereKeyNot($zone->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['slug' => 'The slug has already been taken for this city.']);
        }
        $data['status'] = $data['status'] ?? 'active';
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
