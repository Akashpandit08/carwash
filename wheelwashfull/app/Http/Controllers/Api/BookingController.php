<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Rating;
use App\Models\Service;
use App\Models\Vehicle;
use App\Http\Requests\Api\CreateBookingRequest;
use App\Http\Resources\Api\BookingDetailResource;
use App\Http\Resources\Api\BookingResource;
use App\Http\Resources\Api\TrackingResource;
use App\Constants\WashType;
use App\Services\BookingService;
use App\Services\LocationTrackingService;
use App\Services\SlotAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService) {}

    public function slots(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
            'wash_type' => ['nullable', Rule::in(WashType::ALL)],
            'latitude' => ['nullable', 'required_with:wash_type', 'numeric'],
            'longitude' => ['nullable', 'required_with:wash_type', 'numeric'],
        ]);

        $service = Service::where('id', $request->service_id)
            ->where('is_active', true)
            ->first();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found or inactive.',
            ], 422);
        }

        if ($request->filled('wash_type')) {
            $slots = app(SlotAvailabilityService::class)->getAvailableSlots(
                $request->wash_type,
                (float) $request->latitude,
                (float) $request->longitude,
                $request->date,
                (int) $request->service_id
            );

            return response()->json([
                'success' => true,
                'date' => $request->date,
                'wash_type' => $request->wash_type,
                'slots' => $slots,
            ]);
        }

        $slots = $this->bookingService->generateTimeSlots($request->date, $request->service_id);

        return response()->json([
            'success' => true,
            'data' => $slots
        ]);
    }

    public function availableSlots(Request $request, SlotAvailabilityService $slotAvailabilityService)
    {
        $validated = $request->validate([
            'service_id' => 'nullable|exists:services,id',
            'wash_type' => ['required', Rule::in(WashType::ALL)],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $service = !empty($validated['service_id'])
            ? Service::where('id', $validated['service_id'])->where('is_active', true)->first()
            : null;

        if (!empty($validated['service_id']) && !$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found or inactive.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'date' => $validated['date'],
            'wash_type' => $validated['wash_type'],
            'slots' => $slotAvailabilityService->getAvailableSlots(
                $validated['wash_type'],
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $validated['date'],
                isset($validated['service_id']) ? (int) $validated['service_id'] : null
            ),
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'coupon_code' => 'required|string',
        ]);

        $service = Service::where('id', $request->service_id)
            ->where('is_active', true)
            ->first();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found or inactive.',
            ], 422);
        }

        $pricing = $this->bookingService->calculatePricing($request->service_id, $request->coupon_code);

        return response()->json($pricing, $pricing['success'] ? 200 : 400);
    }

    public function store(CreateBookingRequest $request)
    {
        $validated = $request->validated();
        $validated['slot_time'] = $validated['slot_time'] ?? $validated['booking_time'];

        $service = Service::where('id', $validated['service_id'])
            ->where('is_active', true)
            ->first();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found or inactive.',
            ], 422);
        }

        $vehicle = Vehicle::where('id', $validated['vehicle_id'])
            ->where('user_id', auth()->id())
            ->first();

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found.',
            ], 422);
        }

        // Validate slot availability
        if (!empty($validated['wash_type'])) {
            $geoSlots = app(SlotAvailabilityService::class)->getAvailableSlots(
                $validated['wash_type'],
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $validated['booking_date'],
                (int) $validated['service_id']
            );

            $selectedSlot = collect($geoSlots)->firstWhere('time', $validated['slot_time']);

            if (!$selectedSlot || empty($selectedSlot['available'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No available slot within 10 KM for selected location.'
                ], 422);
            }
        } else {
            $availableSlots = $this->bookingService->generateTimeSlots($validated['booking_date'], $validated['service_id']);

            if (!in_array($validated['slot_time'], $availableSlots)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected slot is fully booked or unavailable.'
                ], 422);
            }
        }

        $validated['user_id'] = auth()->id();

        try {
            $booking = $this->bookingService->createBooking($validated);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
        $booking->load(['latestPayment']);

        $response = [
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => $booking,
        ];

        // Send Notification
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->sendBookingCreated(auth()->user(), $booking);

        if ($booking->payment_method === 'online' && $booking->latestPayment) {
            $response['payment'] = [
                'id' => $booking->latestPayment->id,
                'payment_reference' => $booking->latestPayment->payment_reference,
                'status' => $booking->latestPayment->status,
                'checkout_url' => url('/customer/payments/' . $booking->latestPayment->id . '/checkout'),
            ];
        }

        return response()->json($response, 201);
    }

    public function index()
    {
        $bookings = Booking::with(['service', 'vehicle', 'latestPayment', 'partner', 'worker', 'pickupDriver', 'deliveryDriver'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => BookingResource::collection($bookings)
        ]);
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $booking->load(['service', 'vehicle', 'rating', 'latestPayment', 'partner', 'worker', 'pickupDriver', 'deliveryDriver', 'pickupAddress', 'dropAddress', 'media', 'statusLogs']);

        return response()->json([
            'success' => true,
            'data' => new BookingDetailResource($booking)
        ]);
    }

    public function track(Booking $booking, LocationTrackingService $trackingService)
    {
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => TrackingResource::collection($trackingService->latestForBooking($booking)),
        ]);
    }

    public function review(Request $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ]);

        $rating = Rating::updateOrCreate(
            ['booking_id' => $booking->id, 'user_id' => auth()->id()],
            $validated + ['partner_id' => $booking->partner_id]
        );

        return response()->json(['success' => true, 'data' => $rating], 201);
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $allowedToCancel = [
            \App\Constants\BookingStatus::PENDING, 
            \App\Constants\BookingStatus::CONFIRMED,
            \App\Constants\BookingStatus::PARTNER_ASSIGNED,
            \App\Constants\BookingStatus::WORKER_ASSIGNED,
            \App\Constants\BookingStatus::PICKUP_DRIVER_ASSIGNED
        ];

        if (!in_array($booking->status, $allowedToCancel)) {
            return response()->json([
                'success' => false,
                'message' => 'Booking cannot be cancelled at this stage.'
            ], 422);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->input('reason', 'Cancelled by customer.')
        ]);

        // Send cancellation push notifications to all involved parties
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->notifyBookingCancelled($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully.',
            'data' => new BookingDetailResource($booking->fresh())
        ]);
    }
}
