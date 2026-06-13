<?php

namespace App\Http\Controllers\Admin;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ServiceCity;
use App\Models\ServiceZone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CityAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['serviceCity', 'serviceZone'])->where('role', UserRole::CITY_ADMIN)->latest();

        if ($request->filled('service_city_id')) {
            $query->where('service_city_id', $request->service_city_id);
        }

        return view('admin.city-admins.index', [
            'admins' => $query->paginate(20),
            'cities' => ServiceCity::orderBy('sort_order')->orderBy('name')->get(),
            'zones' => ServiceZone::with('city')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['password'] = $data['password'] ?? '12345678';
        $data['status'] = $data['status'] ?? 'active';
        User::create($data + ['role' => UserRole::CITY_ADMIN]);

        return back()->with('success', 'City admin created successfully.');
    }

    public function update(Request $request, User $cityAdmin)
    {
        abort_unless($cityAdmin->role === UserRole::CITY_ADMIN, 404);

        $data = $this->validated($request, $cityAdmin);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $cityAdmin->update($data);

        return back()->with('success', 'City admin updated successfully.');
    }

    public function destroy(User $cityAdmin)
    {
        abort_unless($cityAdmin->role === UserRole::CITY_ADMIN, 404);
        $cityAdmin->delete();

        return back()->with('success', 'City admin deleted successfully.');
    }

    private function validated(Request $request, ?User $admin = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:20', Rule::unique('users', 'mobile_number')->ignore($admin?->id)],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($admin?->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'service_city_id' => ['required', 'exists:service_cities,id'],
            'service_zone_id' => ['nullable', 'exists:service_zones,id'],
        ]);
    }
}
