<?php

namespace App\Http\Controllers\Admin;

use App\Constants\BookingStatus;
use App\Constants\UserRole;
use App\Constants\WashType;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CustomerSubscription;
use App\Models\ServiceCity;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\CityScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminPageController extends Controller
{
    public function assignTeam(Request $request, CityScopeService $cityScope)
    {
        $bookings = Booking::with(['user', 'service', 'partner', 'worker', 'pickupDriver', 'serviceCity', 'serviceZone'])
            ->tap(fn ($query) => $cityScope->apply($query, auth()->user()))
            ->when($request->filled('service_city_id'), fn ($query) => $query->where('service_city_id', $request->service_city_id))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->paginate(20);

        $team = User::with(['serviceCity', 'serviceZone', 'partnerProfile', 'workerProfile', 'pickupDriverProfile'])
            ->whereIn('role', [UserRole::PARTNER, UserRole::WORKER, UserRole::PICKUP_DRIVER])
            ->tap(fn ($query) => $cityScope->apply($query, auth()->user()))
            ->when($request->filled('service_city_id'), fn ($query) => $query->where('service_city_id', $request->service_city_id))
            ->get();

        return view('admin.pages.assign-team', compact('bookings', 'team'));
    }

    public function assignTeamStore(Request $request, Booking $booking, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $booking);
        $data = $request->validate([
            'partner_id' => ['nullable', 'exists:users,id'],
            'worker_id' => ['nullable', 'exists:users,id'],
            'pickup_driver_id' => ['nullable', 'exists:users,id'],
            'delivery_driver_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        foreach (['partner_id', 'worker_id', 'pickup_driver_id', 'delivery_driver_id'] as $field) {
            if (!empty($data[$field])) {
                $this->ensureAssignableUser((int) $data[$field], $booking, $field);
            }
        }

        if ($booking->wash_type === WashType::DOOR_TO_DOOR && empty($data['worker_id']) && empty($data['partner_id'])) {
            throw ValidationException::withMessages(['worker_id' => 'Assign a worker or partner for doorstep booking.']);
        }

        if ($booking->wash_type === WashType::PICKUP_WASH && (empty($data['pickup_driver_id']) || empty($data['partner_id']))) {
            throw ValidationException::withMessages(['pickup_driver_id' => 'Assign pickup driver and partner for pickup wash booking.']);
        }

        DB::transaction(function () use ($booking, $data) {
            $assignmentData = collect($data)->only(['partner_id', 'worker_id', 'pickup_driver_id', 'delivery_driver_id'])->filter()->all();
            $booking->forceFill($assignmentData + [
                'status' => $this->assignmentStatus($booking, $assignmentData),
            ])->save();

            $booking->assignments()->create($assignmentData + [
                'assigned_by_admin_id' => auth()->id(),
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
                'status' => 'active',
                'notes' => $data['notes'] ?? 'Assigned from admin panel',
            ]);
        });

        return back()->with('success', 'Team assigned successfully.');
    }

    public function teamManagement(Request $request)
    {
        $users = User::with(['serviceCity', 'serviceZone'])
            ->whereIn('role', [UserRole::PARTNER, UserRole::WORKER, UserRole::PICKUP_DRIVER])
            ->when($request->filled('service_city_id'), fn ($query) => $query->where('service_city_id', $request->service_city_id))
            ->latest()
            ->paginate(25);

        return view('admin.pages.team-management', compact('users'));
    }

    private function ensureAssignableUser(int $userId, Booking $booking, string $field): void
    {
        $expectedRole = match ($field) {
            'partner_id' => UserRole::PARTNER,
            'worker_id' => UserRole::WORKER,
            default => UserRole::PICKUP_DRIVER,
        };
        $user = User::findOrFail($userId);

        if ($user->role !== $expectedRole || (int) $user->service_city_id !== (int) $booking->service_city_id) {
            throw ValidationException::withMessages([$field => 'Selected team member must belong to the same city and expected role.']);
        }
    }

    private function assignmentStatus(Booking $booking, array $data): string
    {
        if (!empty($data['pickup_driver_id'])) {
            return BookingStatus::PICKUP_DRIVER_ASSIGNED;
        }
        if (!empty($data['worker_id'])) {
            return BookingStatus::WORKER_ASSIGNED;
        }
        if (!empty($data['partner_id'])) {
            return BookingStatus::PARTNER_ASSIGNED;
        }

        return $booking->status;
    }

    public function subscriptions(Request $request)
    {
        $plans = SubscriptionPlan::with(['serviceCity', 'serviceZone'])
            ->when($request->filled('service_city_id'), fn ($query) => $query->where('service_city_id', $request->service_city_id))
            ->latest()
            ->paginate(15);
        $subscriptions = CustomerSubscription::with(['user', 'subscriptionPlan', 'serviceCity'])
            ->when($request->filled('service_city_id'), fn ($query) => $query->where('service_city_id', $request->service_city_id))
            ->latest()
            ->take(10)
            ->get();

        return view('admin.pages.subscriptions', compact('plans', 'subscriptions'));
    }

    public function earnings(Request $request)
    {
        $bookings = Booking::query()
            ->when($request->filled('service_city_id'), fn ($query) => $query->where('service_city_id', $request->service_city_id));

        return view('admin.pages.earnings', [
            'totalRevenue' => (clone $bookings)->where('payment_status', 'paid')->sum('total_amount'),
            'codRevenue' => (clone $bookings)->where('payment_status', 'paid')->where('payment_method', 'cod')->sum('total_amount'),
            'onlineRevenue' => (clone $bookings)->where('payment_status', 'paid')->where('payment_method', 'online')->sum('total_amount'),
            'cityRows' => ServiceCity::orderBy('sort_order')->orderBy('name')->get()->map(function (ServiceCity $city) {
                $cityBookings = Booking::where('service_city_id', $city->id)->where('payment_status', 'paid');
                return ['city' => $city, 'revenue' => (clone $cityBookings)->sum('total_amount'), 'bookings' => (clone $cityBookings)->count()];
            }),
        ]);
    }

    public function settings()
    {
        return view('admin.pages.settings');
    }
}
