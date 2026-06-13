<?php

namespace App\Http\Controllers\Admin;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Models\PartnerProfile;
use App\Models\ServiceCity;
use App\Models\ServiceZone;
use App\Models\User;
use App\Services\CityScopeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    /**
     * Display list of partners
     */
    public function index(Request $request, CityScopeService $cityScope)
    {
        $query = User::query()
            ->where('role', UserRole::PARTNER)
            ->with(['partnerProfile', 'serviceCity', 'serviceZone'])
            ->withCount('assignedBookings')
            ->withCount([
                'assignedBookings as completed_bookings' => fn ($bookingQuery) => $bookingQuery->where('status', 'completed'),
            ]);

        $cityScope->apply($query, auth()->user());

        // Search by name or mobile
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('mobile_number', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhereHas('partnerProfile', function ($profileQuery) use ($request) {
                      $profileQuery->where('business_name', 'like', '%' . $request->search . '%')
                          ->orWhere('service_area', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->whereHas('partnerProfile', fn ($profileQuery) => $profileQuery->where('current_status', $request->status));
        }

        if ($request->filled('service_city_id')) {
            $query->where('service_city_id', $request->service_city_id);
        }

        $sortBy = in_array($request->sort_by, ['created_at', 'name'], true) ? $request->sort_by : 'created_at';
        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $partners = $query->paginate(20);
        $cities = $this->citiesForAdmin($cityScope);

        return view('admin.partners.index', compact('partners', 'cities'));
    }

    public function create(CityScopeService $cityScope)
    {
        return view('admin.partners.create', [
            'partner' => new User(['role' => UserRole::PARTNER]),
            'profile' => new PartnerProfile(['current_status' => 'active', 'commission_type' => 'percentage']),
            'cities' => $this->citiesForAdmin($cityScope),
            'zones' => ServiceZone::with('city')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, CityScopeService $cityScope)
    {
        $validated = $this->validatePartner($request);
        $cityId = $cityScope->allowedCityId(auth()->user(), $validated['service_city_id'] ?? null);

        DB::transaction(function () use ($validated, $cityId) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => ($validated['email'] ?? null) ?: Str::uuid() . '@wheelwash.local',
                'mobile_number' => $validated['mobile_number'],
                'password' => ($validated['password'] ?? null) ?: '12345678',
                'role' => UserRole::PARTNER,
                'status' => 'active',
                'service_city_id' => $cityId,
                'service_zone_id' => $validated['service_zone_id'] ?? null,
            ]);

            $user->partnerProfile()->create($this->profileData($validated));
        });

        return redirect()->route('admin.partners.index')->with('success', 'Partner created successfully.');
    }

    public function edit(User $partner, CityScopeService $cityScope)
    {
        $this->ensurePartner($partner, $cityScope);

        return view('admin.partners.edit', [
            'partner' => $partner->load(['partnerProfile', 'serviceCity', 'serviceZone']),
            'profile' => $partner->partnerProfile ?: new PartnerProfile(['current_status' => 'active', 'commission_type' => 'percentage']),
            'cities' => $this->citiesForAdmin($cityScope),
            'zones' => ServiceZone::with('city')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $partner, CityScopeService $cityScope)
    {
        $this->ensurePartner($partner, $cityScope);

        $validated = $this->validatePartner($request, $partner);
        $cityId = $cityScope->allowedCityId(auth()->user(), $validated['service_city_id'] ?? null);

        DB::transaction(function () use ($validated, $partner, $cityId) {
            $userData = [
                'name' => $validated['name'],
                'email' => ($validated['email'] ?? null) ?: $partner->email,
                'mobile_number' => $validated['mobile_number'],
                'service_city_id' => $cityId,
                'service_zone_id' => $validated['service_zone_id'] ?? null,
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = $validated['password'];
            }

            $partner->update($userData);
            $partner->partnerProfile()->updateOrCreate(
                ['user_id' => $partner->id],
                $this->profileData($validated)
            );
        });

        return redirect()->route('admin.partners.show', $partner)->with('success', 'Partner updated successfully.');
    }

    public function toggleStatus(User $partner, CityScopeService $cityScope)
    {
        $this->ensurePartner($partner, $cityScope);

        $profile = $partner->partnerProfile()->firstOrCreate(
            ['user_id' => $partner->id],
            [
                'business_name' => $partner->name,
                'current_status' => 'active',
                'commission_type' => 'percentage',
                'commission_value' => 0,
            ]
        );

        $profile->update([
            'current_status' => $profile->current_status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Partner status updated.');
    }

    /**
     * Show partner details
     */
    public function show(User $partner, CityScopeService $cityScope)
    {
        $this->ensurePartner($partner, $cityScope);

        $partner->load(['partnerProfile', 'serviceCity', 'serviceZone', 'receivedRatings.booking.service']);

        $assignments       = $partner->assignedBookings()->with(['user', 'service', 'vehicle'])->latest()->get();
        $totalBookings     = $partner->assignedBookings()->count();
        $completedBookings = $partner->assignedBookings()->where('status', 'completed')->count();
        $totalEarnings     = $partner->assignedBookings()->where('payment_status', 'paid')->sum('final_price');
        $ratings           = $partner->receivedRatings()->with(['booking.service', 'user'])->latest()->get();
        $avgRating         = $ratings->avg('rating') ? round($ratings->avg('rating'), 1) : null;

        return view('admin.partners.show', compact(
            'partner', 'assignments', 'totalBookings', 'completedBookings',
            'totalEarnings', 'ratings', 'avgRating'
        ));
    }

    private function validatePartner(Request $request, ?User $partner = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($partner?->id)],
            'mobile_number' => ['required', 'string', 'max:20', Rule::unique('users', 'mobile_number')->ignore($partner?->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'service_city_id' => ['nullable', 'exists:service_cities,id'],
            'service_zone_id' => ['nullable', 'exists:service_zones,id'],
            'business_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'service_area' => ['nullable', 'string', 'max:255'],
            'service_radius' => ['nullable', 'integer', 'min:0'],
            'current_status' => ['nullable', Rule::in(['active', 'inactive'])],
            'commission_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'commission_value' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function profileData(array $validated): array
    {
        return [
            'business_name' => $validated['business_name'],
            'address' => $validated['address'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'service_area' => $validated['service_area'] ?? null,
            'service_radius' => $validated['service_radius'] ?? 5000,
            'current_status' => $validated['current_status'] ?? 'active',
            'commission_type' => $validated['commission_type'] ?? 'percentage',
            'commission_value' => $validated['commission_value'] ?? 0,
        ];
    }

    private function ensurePartner(User $partner, CityScopeService $cityScope): void
    {
        if ($partner->role !== UserRole::PARTNER) {
            abort(404);
        }

        $cityScope->ensureCanAccessModel(auth()->user(), $partner);
    }

    private function citiesForAdmin(CityScopeService $cityScope)
    {
        $admin = auth()->user();

        if ($cityScope->isSuperAdmin($admin)) {
            return ServiceCity::orderBy('sort_order')->orderBy('name')->get();
        }

        return ServiceCity::where('id', $admin->service_city_id)->orderBy('name')->get();
    }
}
