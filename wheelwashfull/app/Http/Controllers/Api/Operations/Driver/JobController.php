<?php

namespace App\Http\Controllers\Api\Operations\Driver;

use App\Constants\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateBookingStatusRequest;
use App\Http\Requests\Api\UploadBookingMediaRequest;
use App\Http\Resources\Api\BookingDetailResource;
use App\Http\Resources\Api\BookingMediaResource;
use App\Http\Resources\Api\BookingResource;
use App\Models\Booking;
use App\Services\BookingStateService;
use App\Services\DistanceService;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class JobController extends Controller
{
    public function dashboard()
    {
        $jobs = $this->baseQuery();

        return response()->json(['success' => true, 'data' => [
            'upcoming_pickups' => (clone $jobs)->whereIn('status', [BookingStatus::PICKUP_DRIVER_ASSIGNED, BookingStatus::DRIVER_ON_THE_WAY, BookingStatus::REACHED_LOCATION])->count(),
            'upcoming_deliveries' => (clone $jobs)->whereIn('status', [BookingStatus::SERVICE_COMPLETED, BookingStatus::OUT_FOR_DELIVERY])->count(),
            'completed_trips' => (clone $jobs)->where('status', BookingStatus::COMPLETED)->count(),
            'today_earnings' => (clone $jobs)->whereDate('updated_at', today())->where('status', BookingStatus::COMPLETED)->sum('pickup_fee'),
            'is_online' => auth()->user()->pickupDriverProfile?->current_status === 'available',
            'active_job' => (clone $jobs)->whereNotIn('status', [BookingStatus::COMPLETED, BookingStatus::CANCELLED])->oldest('booking_date')->first(),
        ]]);
    }

    public function index(Request $request)
    {
        $query = $this->baseQuery()->with(['service', 'vehicle', 'user', 'partner', 'pickupAddress', 'dropAddress']);

        match ($request->query('tab', 'pickup')) {
            'delivery' => $query->whereIn('status', [BookingStatus::SERVICE_COMPLETED, BookingStatus::OUT_FOR_DELIVERY, BookingStatus::DELIVERED]),
            'completed' => $query->where('status', BookingStatus::COMPLETED),
            default => $query->whereIn('status', [BookingStatus::PICKUP_DRIVER_ASSIGNED, BookingStatus::DRIVER_ON_THE_WAY, BookingStatus::REACHED_LOCATION, BookingStatus::CAR_PICKED_UP, BookingStatus::REACHED_PARTNER]),
        };

        return BookingResource::collection($query->orderBy('slot_time')->paginate($request->query('per_page', 15)))
            ->additional(['success' => true]);
    }

    public function show(Booking $booking)
    {
        $this->authorizeJob($booking);

        return response()->json(['success' => true, 'data' => new BookingDetailResource($booking->load(['service', 'vehicle', 'user', 'partner', 'media', 'statusLogs', 'pickupAddress', 'dropAddress']))]);
    }

    public function startPickupTravel(Booking $booking, BookingStateService $state)
    {
        return $this->transition($booking, $state, BookingStatus::DRIVER_ON_THE_WAY, 'Pickup driver started travel to customer');
    }

    public function arrivedCustomer(Request $request, Booking $booking, BookingStateService $state, DistanceService $distance)
    {
        $this->validateArrival($request, $booking, $distance, 300, 'customer');

        return $this->transition($booking, $state, BookingStatus::REACHED_LOCATION, 'Pickup driver reached customer');
    }

    public function pickupVehicle(Booking $booking, BookingStateService $state, MediaUploadService $media)
    {
        $this->authorizeJob($booking);
        $media->assertCanStatus($booking, BookingStatus::CAR_PICKED_UP);

        return $this->transition($booking, $state, BookingStatus::CAR_PICKED_UP, 'Pickup proof uploaded; vehicle picked up');
    }

    public function arrivedPartner(Request $request, Booking $booking, BookingStateService $state, DistanceService $distance)
    {
        $this->validateArrival($request, $booking, $distance, 500, 'partner');

        $response = $this->transition($booking, $state, BookingStatus::REACHED_PARTNER, 'Vehicle reached washing center');

        if ($booking->fresh()->partner_id) {
            try {
                $state->transition($booking->fresh(), BookingStatus::PARTNER_ASSIGNED, auth()->user(), 'Partner already assigned; vehicle reached washing center');
            } catch (InvalidArgumentException) {
                //
            }
        }

        return $response;
    }

    public function startDelivery(Booking $booking, BookingStateService $state)
    {
        return $this->transition($booking, $state, BookingStatus::OUT_FOR_DELIVERY, 'Pickup driver started delivery');
    }

    public function arrivedDelivery(Request $request, Booking $booking, BookingStateService $state, DistanceService $distance)
    {
        $this->validateArrival($request, $booking, $distance, 300, 'delivery');

        return $this->transition($booking, $state, BookingStatus::REACHED_DELIVERY_LOCATION, 'Pickup driver reached delivery location');
    }

    public function deliverVehicle(Booking $booking, BookingStateService $state, MediaUploadService $media)
    {
        $this->authorizeJob($booking);
        $media->assertCanStatus($booking, BookingStatus::DELIVERED);

        return $this->transition($booking, $state, BookingStatus::DELIVERED, 'Delivery proof uploaded; vehicle delivered');
    }

    public function collectCashComplete(Request $request, Booking $booking, BookingStateService $state)
    {
        $this->authorizeJob($booking);
        abort_unless(in_array(strtolower((string) $booking->payment_method), ['cash', 'cod'], true), 422, 'Cash collection is only allowed for COD bookings.');

        $booking = $state->transition($booking, BookingStatus::CASH_COLLECTED, auth()->user(), 'Cash collected');
        $booking = $state->transition($booking, BookingStatus::COMPLETED, auth()->user(), 'Booking completed after cash collection');

        return response()->json(['success' => true, 'data' => new BookingResource($booking)]);
    }

    public function complete(Booking $booking, BookingStateService $state)
    {
        $this->authorizeJob($booking);
        abort_if(in_array(strtolower((string) $booking->payment_method), ['cash', 'cod'], true), 422, 'Collect cash before completing this booking.');

        return $this->transition($booking, $state, BookingStatus::COMPLETED, 'Booking completed');
    }

    public function status(UpdateBookingStatusRequest $request, Booking $booking, BookingStateService $state, MediaUploadService $media)
    {
        $this->authorizeJob($booking);
        abort_unless(in_array($request->status, [BookingStatus::DRIVER_ON_THE_WAY, BookingStatus::REACHED_LOCATION, BookingStatus::CAR_PICKED_UP, BookingStatus::REACHED_PARTNER, BookingStatus::OUT_FOR_DELIVERY, BookingStatus::REACHED_DELIVERY_LOCATION, BookingStatus::DELIVERED], true), 422, 'Invalid pickup driver action.');
        $media->assertCanStatus($booking, $request->status);

        return $this->transition($booking, $state, $request->status, $request->note);
    }

    public function media(UploadBookingMediaRequest $request, Booking $booking, MediaUploadService $service)
    {
        $this->authorizeJob($booking);
        $media = $service->upload($booking, auth()->user(), $request->file('file'), $request->type);

        return response()->json(['success' => true, 'data' => new BookingMediaResource($media)], 201);
    }

    public function earnings()
    {
        $completedBookings = $this->baseQuery()->where('status', BookingStatus::COMPLETED)->get();
        $transactions = $completedBookings->map(fn ($booking) => [
            'id' => $booking->id,
            'booking_id' => $booking->id,
            'amount' => $booking->pickup_fee ?? 20,
            'status' => 'completed',
            'date' => $booking->updated_at->toDateString(),
        ]);

        return response()->json(['success' => true, 'data' => ['total_earnings' => $transactions->sum('amount'), 'transactions' => $transactions]]);
    }

    public function profile()
    {
        return response()->json(['success' => true, 'data' => auth()->user()->load('pickupDriverProfile')]);
    }

    public function updateProfile(Request $request)
    {
        auth()->user()->pickupDriverProfile()->updateOrCreate(['user_id' => auth()->id()], $request->only(['vehicle_type', 'license_number', 'service_area', 'service_radius', 'latitude', 'longitude']));

        return $this->profile();
    }

    protected function baseQuery()
    {
        return Booking::query()
            ->where('pickup_driver_id', auth()->id())
            ->where(function ($query) {
                $query->whereIn('wash_type', ['pickup_wash', 'pickup_drop'])
                    ->orWhere('service_mode', 'pickup_drop');
            });
    }

    protected function authorizeJob(Booking $booking): void
    {
        abort_unless((int) $booking->pickup_driver_id === (int) auth()->id(), 403);
        abort_unless(in_array($booking->wash_type, ['pickup_wash', 'pickup_drop'], true) || $booking->service_mode === 'pickup_drop', 403, 'Pickup drivers can only handle pickup/drop bookings.');
    }

    protected function transition(Booking $booking, BookingStateService $state, string $newStatus, ?string $note = null)
    {
        $this->authorizeJob($booking);

        try {
            $booking = $state->transition($booking, $newStatus, auth()->user(), $note);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => new BookingResource($booking->load(['service', 'vehicle', 'user', 'partner']))]);
    }

    protected function validateArrival(Request $request, Booking $booking, DistanceService $distance, int $allowedMeters, string $destination): void
    {
        $this->authorizeJob($booking);
        $data = $request->validate(['latitude' => ['required', 'numeric'], 'longitude' => ['required', 'numeric']]);
        [$targetLat, $targetLng] = $this->targetCoordinates($booking, $destination);
        $meters = $distance->haversineDistance((float) $data['latitude'], (float) $data['longitude'], $targetLat, $targetLng) * 1000;

        if ($meters > $allowedMeters) {
            abort(response()->json([
                'success' => false,
                'message' => 'You are too far from the destination to perform this action.',
                'distance_meters' => round($meters),
                'allowed_radius_meters' => $allowedMeters,
            ], 422));
        }
    }

    protected function targetCoordinates(Booking $booking, string $destination): array
    {
        if ($destination === 'partner' && $booking->partner?->partnerProfile?->latitude) {
            return [(float) $booking->partner->partnerProfile->latitude, (float) $booking->partner->partnerProfile->longitude];
        }

        if ($destination === 'delivery' && $booking->dropAddress?->latitude) {
            return [(float) $booking->dropAddress->latitude, (float) $booking->dropAddress->longitude];
        }

        if ($booking->pickupAddress?->latitude) {
            return [(float) $booking->pickupAddress->latitude, (float) $booking->pickupAddress->longitude];
        }

        return [(float) $booking->latitude, (float) $booking->longitude];
    }
}
