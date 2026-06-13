<?php

namespace App\Http\Controllers\Admin;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Models\PartnerProfile;
use App\Models\PickupDriverProfile;
use App\Models\ServiceCity;
use App\Models\ServiceZone;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Services\CityScopeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    private const CONFIG = [
        'partners' => [
            'role' => UserRole::PARTNER,
            'relation' => 'partnerProfile',
            'profile' => PartnerProfile::class,
            'title' => 'Partners',
            'statuses' => ['active', 'inactive'],
        ],
        'workers' => [
            'role' => UserRole::WORKER,
            'relation' => 'workerProfile',
            'profile' => WorkerProfile::class,
            'title' => 'Workers',
            'statuses' => ['available', 'busy', 'inactive'],
        ],
        'pickup-drivers' => [
            'role' => UserRole::PICKUP_DRIVER,
            'relation' => 'pickupDriverProfile',
            'profile' => PickupDriverProfile::class,
            'title' => 'Pickup Drivers',
            'statuses' => ['available', 'busy', 'inactive'],
        ],
    ];

    public function index(Request $request, string $type, CityScopeService $cityScope)
    {
        $config = $this->config($type);
        $relation = $config['relation'];

        $query = User::query()
            ->where('role', $config['role'])
            ->with([$relation, 'serviceCity', 'serviceZone']);

        $cityScope->apply($query, auth()->user());
        $this->applyFilters($query, $request, $relation);

        return view('admin.team.index', [
            'type' => $type,
            'config' => $config,
            'users' => $query->latest()->paginate(20),
            'cities' => $this->citiesForAdmin($cityScope),
            'zones' => ServiceZone::with('city')->orderBy('name')->get(),
        ]);
    }

    public function create(string $type, CityScopeService $cityScope)
    {
        $config = $this->config($type);

        return view('admin.team.form', [
            'type' => $type,
            'config' => $config,
            'user' => new User(['role' => $config['role'], 'service_city_id' => $cityScope->allowedCityId(auth()->user())]),
            'profile' => new $config['profile'](['current_status' => $config['statuses'][0]]),
            'cities' => $this->citiesForAdmin($cityScope),
            'zones' => ServiceZone::with('city')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, string $type, CityScopeService $cityScope)
    {
        $config = $this->config($type);
        $data = $this->validated($request, $type, $config);
        $cityId = $cityScope->allowedCityId(auth()->user(), $data['service_city_id'] ?? null);

        DB::transaction(function () use ($data, $config, $cityId) {
            $user = User::create([
                'name' => $data['name'],
                'email' => ($data['email'] ?? null) ?: Str::uuid() . '@wheelwash.local',
                'mobile_number' => $data['mobile_number'],
                'password' => ($data['password'] ?? null) ?: '12345678',
                'role' => $config['role'],
                'status' => 'active',
                'service_city_id' => $cityId,
                'service_zone_id' => $data['service_zone_id'] ?? null,
            ]);

            $user->{$config['relation']}()->create($this->profileData($data, $config['role']));
        });

        return redirect()->route('admin.team.index', ['type' => $type] + $this->cityQuery())->with('success', $config['title'] . ' created successfully.');
    }

    public function edit(string $type, User $user, CityScopeService $cityScope)
    {
        $config = $this->config($type);
        $this->ensureTeamUser($user, $config, $cityScope);

        return view('admin.team.form', [
            'type' => $type,
            'config' => $config,
            'user' => $user->load([$config['relation'], 'serviceCity', 'serviceZone']),
            'profile' => $user->{$config['relation']} ?: new $config['profile'](['current_status' => $config['statuses'][0]]),
            'cities' => $this->citiesForAdmin($cityScope),
            'zones' => ServiceZone::with('city')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $type, User $user, CityScopeService $cityScope)
    {
        $config = $this->config($type);
        $this->ensureTeamUser($user, $config, $cityScope);
        $data = $this->validated($request, $type, $config, $user);
        $cityId = $cityScope->allowedCityId(auth()->user(), $data['service_city_id'] ?? null);

        DB::transaction(function () use ($data, $user, $config, $cityId) {
            $userData = [
                'name' => $data['name'],
                'email' => ($data['email'] ?? null) ?: $user->email,
                'mobile_number' => $data['mobile_number'],
                'service_city_id' => $cityId,
                'service_zone_id' => $data['service_zone_id'] ?? null,
            ];

            if (!empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $user->update($userData);
            $user->{$config['relation']}()->updateOrCreate(
                ['user_id' => $user->id],
                $this->profileData($data, $config['role'])
            );
        });

        return redirect()->route('admin.team.index', ['type' => $type] + $this->cityQuery())->with('success', $config['title'] . ' updated successfully.');
    }

    public function toggle(string $type, User $user, CityScopeService $cityScope)
    {
        $config = $this->config($type);
        $this->ensureTeamUser($user, $config, $cityScope);
        $profile = $user->{$config['relation']}()->firstOrCreate(
            ['user_id' => $user->id],
            $this->profileData(['current_status' => $config['statuses'][0]], $config['role'])
        );
        $inactive = 'inactive';
        $profile->update(['current_status' => $profile->current_status === $inactive ? $config['statuses'][0] : $inactive]);

        return back()->with('success', 'Status updated.');
    }

    private function applyFilters(Builder $query, Request $request, string $relation): void
    {
        if ($request->filled('service_city_id')) {
            $query->where('service_city_id', $request->service_city_id);
        }
        if ($request->filled('status')) {
            $query->whereHas($relation, fn (Builder $profile) => $profile->where('current_status', $request->status));
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function (Builder $nested) use ($search, $relation) {
                $nested->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas($relation, fn (Builder $profile) => $profile->where('service_area', 'like', "%{$search}%"));
            });
        }
    }

    private function validated(Request $request, string $type, array $config, ?User $user = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:20', Rule::unique('users', 'mobile_number')->ignore($user?->id)],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'service_city_id' => ['nullable', 'exists:service_cities,id'],
            'service_zone_id' => ['nullable', 'exists:service_zones,id'],
            'service_area' => ['nullable', 'string', 'max:255'],
            'service_radius' => ['nullable', 'integer', 'min:0'],
            'current_status' => ['nullable', Rule::in($config['statuses'])],
            'location_lat' => ['nullable', 'numeric'],
            'location_lng' => ['nullable', 'numeric'],
        ];

        if ($type === 'partners') {
            $rules += [
                'business_name' => ['required', 'string', 'max:255'],
                'address' => ['nullable', 'string'],
            ];
        } elseif ($type === 'workers') {
            $rules['skills'] = ['nullable', 'string'];
        } else {
            $rules += [
                'vehicle_type' => ['nullable', 'string', 'max:255'],
                'license_number' => ['nullable', 'string', 'max:255'],
            ];
        }

        return $request->validate($rules);
    }

    private function profileData(array $data, string $role): array
    {
        $base = [
            'service_area' => $data['service_area'] ?? null,
            'service_radius' => $data['service_radius'] ?? 5000,
            'latitude' => $data['location_lat'] ?? null,
            'longitude' => $data['location_lng'] ?? null,
            'current_status' => $data['current_status'] ?? ($role === UserRole::PARTNER ? 'active' : 'available'),
        ];

        return match ($role) {
            UserRole::PARTNER => $base + [
                'business_name' => $data['business_name'] ?? '',
                'address' => $data['address'] ?? null,
                'commission_type' => 'percentage',
                'commission_value' => 0,
            ],
            UserRole::WORKER => $base + [
                'skills' => collect(explode(',', $data['skills'] ?? ''))->map(fn ($skill) => trim($skill))->filter()->values()->all(),
            ],
            default => $base + [
                'vehicle_type' => $data['vehicle_type'] ?? null,
                'license_number' => $data['license_number'] ?? null,
            ],
        };
    }

    private function ensureTeamUser(User $user, array $config, CityScopeService $cityScope): void
    {
        abort_unless($user->role === $config['role'], 404);
        $cityScope->ensureCanAccessModel(auth()->user(), $user);
    }

    private function config(string $type): array
    {
        abort_unless(isset(self::CONFIG[$type]), 404);

        return self::CONFIG[$type];
    }

    private function citiesForAdmin(CityScopeService $cityScope)
    {
        $admin = auth()->user();

        return $cityScope->isSuperAdmin($admin)
            ? ServiceCity::orderBy('sort_order')->orderBy('name')->get()
            : ServiceCity::whereKey($admin->service_city_id)->get();
    }

    private function cityQuery(): array
    {
        return request('service_city_id') ? ['service_city_id' => request('service_city_id')] : [];
    }
}
