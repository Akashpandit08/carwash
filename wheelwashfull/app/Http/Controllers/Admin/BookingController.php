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
        $partners = User::where('role', 'partner')->get();

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
        $booking->load(['user', 'service', 'vehicle', 'partner', 'images', 'rating', 'statusHistories.changedByUser', 'assignments.partner']);
        $partners = User::where('role', 'partner')->get();
        $validNextStatuses = $this->validNextStatuses($booking->status);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'booking' => $booking,
                    'partners' => $partners,
                    'valid_next_statuses' => $validNextStatuses,
                ],
            ]);
        }

        return view('admin.bookings.show', compact('booking', 'partners', 'validNextStatuses'));
    }

    public function assignPartner(Request $request, Booking $booking)
    {
        $request->validate([
            'partner_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return $this->bookingResponse($request, false, 'Cannot reassign a completed or cancelled booking.', 422);
        }

        $partner = User::findOrFail($request->partner_id);
        if ($partner->role !== 'partner') {
            return $this->bookingResponse($request, false, 'Selected user is not a partner.', 422);
        }

        $hasConflict = Booking::where('partner_id', $partner->id)
            ->whereDate('booking_date', $booking->booking_date)
            ->where('slot_time', $booking->slot_time)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where('id', '!=', $booking->id)
            ->exists();

        if ($hasConflict) {
            return $this->bookingResponse($request, false, 'Selected partner already has a booking in this slot.', 422);
        }

        $booking->assignments()->create([
            'partner_id' => $partner->id,
            'assigned_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        $booking->update([
            'partner_id' => $partner->id,
            'status' => 'assigned',
        ]);

        $this->partnerJobService->recordStatusChange($booking, 'assigned', auth()->user(), 'admin', $request->notes);

        if ($booking->user) {
            $this->notificationService->sendPartnerAssigned($partner, $booking->user, $booking);
        }

        return $this->bookingResponse($request, true, 'Partner assigned successfully. Notifications sent.');
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
