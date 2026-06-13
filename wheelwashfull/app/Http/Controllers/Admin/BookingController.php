<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\PaymentService;
use App\Services\PartnerJobService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    private const ADMIN_STATUS_TRANSITIONS = [
        'pending' => ['assigned', 'cancelled'],
        'assigned' => ['pending', 'accepted', 'cancelled'],
        'accepted' => ['on_the_way', 'cancelled'],
        'on_the_way' => ['started', 'cancelled'],
        'started' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function __construct(
        protected PartnerJobService $partnerJobService,
        protected NotificationService $notificationService,
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'service', 'vehicle', 'partner']);

        if ($request->filled('service_city_id')) {
            $cityId = $request->service_city_id;
            $query->where(function($q) use ($cityId) {
                $q->where('service_city_id', $cityId)
                  ->orWhereHas('worker', fn($w) => $w->where('service_city_id', $cityId))
                  ->orWhereHas('partner', fn($p) => $p->where('service_city_id', $cityId))
                  ->orWhereHas('pickupDriver', fn($d) => $d->where('service_city_id', $cityId));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('booking_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('booking_date', '<=', $request->date_to);
        }
        // Also support legacy single 'date' filter
        if ($request->filled('date')) {
            $query->whereDate('booking_date', $request->date);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('partner_id')) {
            if ($request->partner_id === 'unassigned') {
                $query->whereNull('partner_id');
            } else {
                $query->where('partner_id', $request->partner_id);
            }
        }
        if ($request->filled('customer_mobile')) {
            $mobile = $request->customer_mobile;
            $query->whereHas('user', function ($q) use ($mobile) {
                $q->where('mobile_number', 'like', "%{$mobile}%");
            });
        }

        $bookings = $query->orderBy('booking_date', 'desc')->orderBy('slot_time')->paginate(15);
        $partners = User::where('role', 'partner')
            ->when($request->filled('service_city_id'), fn ($query) => $query->where('service_city_id', $request->service_city_id))
            ->get();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'bookings' => $bookings,
                    'partners' => $partners,
                ],
            ]);
        }

        return view('admin.bookings.index', compact('bookings', 'partners'));
    }

    public function show(Request $request, Booking $booking)
    {
        $booking->load(['user', 'service', 'vehicle', 'partner', 'images', 'rating', 'statusHistories.changedByUser', 'assignments.partner', 'assignments.worker', 'assignments.pickupDriver', 'assignments.deliveryDriver']);
        $partners = User::where('role', 'partner')
            ->when(request()->filled('service_city_id'), fn ($query) => $query->where('service_city_id', request('service_city_id')))
            ->get();
        $workers = User::where('role', 'worker')
            ->when(request()->filled('service_city_id'), fn ($query) => $query->where('service_city_id', request('service_city_id')))
            ->get();
        $pickupDrivers = User::where('role', 'pickup_driver')
            ->when(request()->filled('service_city_id'), fn ($query) => $query->where('service_city_id', request('service_city_id')))
            ->get();
            
        $validNextStatuses = $this->validNextStatuses($booking->status);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'booking' => $booking,
                    'partners' => $partners,
                    'workers' => $workers,
                    'pickup_drivers' => $pickupDrivers,
                    'valid_next_statuses' => $validNextStatuses,
                ],
            ]);
        }

        return view('admin.bookings.show', compact('booking', 'partners', 'workers', 'pickupDrivers', 'validNextStatuses'));
    }

    public function assignTeam(Request $request, Booking $booking)
    {
        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return $this->bookingResponse($request, false, 'Cannot reassign a completed or cancelled booking.', 422);
        }

        if ($booking->wash_type === 'door_to_door' || $booking->service_mode === 'doorstep') {
            $request->validate([
                'worker_id' => 'required|exists:users,id',
                'notes' => 'nullable|string',
            ]);

            $worker = User::findOrFail($request->worker_id);

            $hasConflict = Booking::where('worker_id', $worker->id)
                ->whereDate('booking_date', $booking->booking_date)
                ->where('slot_time', $booking->slot_time)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->where('id', '!=', $booking->id)
                ->exists();

            if ($hasConflict) {
                return $this->bookingResponse($request, false, 'Selected worker already has a booking in this slot.', 422);
            }

            $booking->assignments()->create([
                'worker_id' => $worker->id,
                'assigned_by' => auth()->id(),
                'notes' => $request->notes,
                'status' => 'active',
                'assigned_at' => now(),
            ]);

            $booking->update([
                'worker_id' => $worker->id,
                'service_city_id' => $worker->service_city_id,
                'service_zone_id' => $worker->service_zone_id,
                'status' => 'assigned',
            ]);

            $this->partnerJobService->recordStatusChange($booking, 'assigned', auth()->user(), 'admin', $request->notes);

            if ($booking->user) {
                // Assuming workerAssigned exists, else it won't crash if handled safely.
                if (method_exists($this->notificationService, 'workerAssigned')) {
                    $this->notificationService->workerAssigned($booking, $worker);
                }
            }

            return $this->bookingResponse($request, true, 'Worker assigned successfully.');
        } else {
            $request->validate([
                'pickup_driver_id' => 'required|exists:users,id',
                'partner_id' => 'required|exists:users,id',
                'delivery_driver_id' => 'required|exists:users,id',
                'notes' => 'nullable|string',
            ]);

            $driver = User::findOrFail($request->pickup_driver_id);
            $partner = User::findOrFail($request->partner_id);
            $deliveryDriver = User::findOrFail($request->delivery_driver_id);

            $booking->assignments()->create([
                'pickup_driver_id' => $driver->id,
                'partner_id' => $partner->id,
                'delivery_driver_id' => $deliveryDriver->id,
                'assigned_by' => auth()->id(),
                'notes' => $request->notes,
                'status' => 'active',
                'assigned_at' => now(),
            ]);

            $booking->update([
                'pickup_driver_id' => $driver->id,
                'partner_id' => $partner->id,
                'delivery_driver_id' => $deliveryDriver->id,
                'service_city_id' => $partner->service_city_id,
                'service_zone_id' => $partner->service_zone_id,
                'status' => 'assigned',
            ]);

            $this->partnerJobService->recordStatusChange($booking, 'assigned', auth()->user(), 'admin', $request->notes);

            if ($booking->user) {
                if (method_exists($this->notificationService, 'pickupDriverAssigned')) {
                    $this->notificationService->pickupDriverAssigned($booking, $driver);
                }
                if (method_exists($this->notificationService, 'partnerAssigned')) {
                    $this->notificationService->partnerAssigned($booking, $partner);
                }
            }

            return $this->bookingResponse($request, true, 'Team assigned successfully.');
        }
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:pending,assigned,accepted,on_the_way,started,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        if (!$this->canAdminMoveStatus($booking->status, $request->status)) {
            return $this->bookingResponse($request, false, "Cannot change status from {$booking->status} to {$request->status}.", 422);
        }

        if (in_array($request->status, ['assigned', 'accepted', 'on_the_way', 'started', 'completed'], true) && !$booking->partner_id) {
            return $this->bookingResponse($request, false, 'Assign a partner before moving this booking into the job workflow.', 422);
        }

        if ($request->status === 'completed') {
            $hasAfterImage = $booking->images()->where('image_type', 'after')->exists();

            if (!$hasAfterImage) {
                return $this->bookingResponse($request, false, 'Upload at least one after image before completing the booking.', 422);
            }
        }

        $booking->update(['status' => $request->status]);

        $this->partnerJobService->recordStatusChange(
            $booking,
            $request->status,
            auth()->user(),
            'admin',
            $request->notes
        );

        return $this->bookingResponse($request, true, 'Booking status updated.');
    }

    public function updatePaymentStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $this->paymentService->syncBookingPaymentStatus($booking, $request->payment_status);

        if ($request->payment_status === 'paid' && $booking->user) {
            $this->notificationService->sendPaymentSuccess($booking->user, $booking);
        }

        return $this->bookingResponse($request, true, 'Payment status updated.');
    }

    private function canAdminMoveStatus(string $fromStatus, string $toStatus): bool
    {
        if ($fromStatus === $toStatus) {
            return true;
        }

        return in_array($toStatus, self::ADMIN_STATUS_TRANSITIONS[$fromStatus] ?? [], true);
    }

    private function validNextStatuses(string $status): array
    {
        return array_values(array_unique(array_merge([$status], self::ADMIN_STATUS_TRANSITIONS[$status] ?? [])));
    }

    private function bookingResponse(Request $request, bool $success, string $message, int $status = 200)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], $status);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
