<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceCityController extends Controller
{
    public function index()
    {
        $cities = ServiceCity::withCount('zones')->orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('admin.cities.index', compact('cities'));
    }

    public function store(Request $request)
    {
        ServiceCity::create($this->validated($request));

        return back()->with('success', 'City created successfully.');
    }

    public function update(Request $request, ServiceCity $city)
    {
        $city->update($this->validated($request, $city));

        return back()->with('success', 'City updated successfully.');
    }

    public function destroy(ServiceCity $city)
    {
        $city->delete();

        return back()->with('success', 'City deleted successfully.');
    }

    private function validated(Request $request, ?ServiceCity $city = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('service_cities', 'slug')->ignore($city?->id)],
            'state' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'coming_soon', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['status'] = $data['status'] ?? 'active';
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
