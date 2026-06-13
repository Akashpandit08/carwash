<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AssignPartnerRequest;
use App\Http\Requests\Api\AssignPickupDriverRequest;
use App\Http\Requests\Api\AssignWorkerRequest;
use App\Http\Requests\Api\UpdateBookingStatusRequest;
use App\Http\Resources\Api\BookingDetailResource;
use App\Http\Resources\Api\BookingMediaResource;
use App\Http\Resources\Api\BookingResource;
use App\Http\Resources\Api\BookingStatusLogResource;
use App\Http\Resources\Api\TrackingResource;
use App\Models\Booking;
use App\Services\AssignmentService;
use App\Services\BookingStateService;
use App\Services\CityScopeService;
use App\Services\LocationTrackingService;
use InvalidArgumentException;

class BookingController extends Controller
{
    public function index(CityScopeService $cityScope)
    {
        $query = Booking::with(['user', 'service', 'vehicle', 'partner', 'worker', 'pickupDriver', 'deliveryDriver']);
        $cityScope->apply($query, auth()->user());

        $bookings = $query->latest()->paginate(request('per_page', 15));

        return BookingResource::collection($bookings)->additional(['success' => true]);
    }

    public function show(Booking $booking, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $booking);

        return response()->json([
            'success' => true,
            'data' => new BookingDetailResource($booking->load([
                'user',
                'service',
                'vehicle',
                'partner',
                'worker',
                'pickupDriver',
                'deliveryDriver',
                'media.uploadedBy',
                'statusLogs.changedBy',
                'payments',
                'payouts.user',
            ])),
        ]);
    }

    public function assignWorker(AssignWorkerRequest $request, Booking $booking, AssignmentService $service)
    {
        try {
            $booking = $service->assignWorker($booking, (int) $request->worker_id, auth()->id());
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => new BookingResource($booking->load(['worker']))]);
    }

    public function assignPartner(AssignPartnerRequest $request, Booking $booking, AssignmentService $service)
    {
        try {
            $booking = $service->assignPartner($booking, (int) $request->partner_id, auth()->id());
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Partner assigned successfully',
            'data' => new BookingResource($booking->load(['partner'])),
        ]);
    }

    public function assignPickupDriver(AssignPickupDriverRequest $request, Booking $booking, AssignmentService $service)
    {
        try {
            $booking = $service->assignPickupDriver($booking, (int) $request->pickup_driver_id, auth()->id());
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => new BookingResource($booking->load(['pickupDriver']))]);
    }

    public function updateStatus(UpdateBookingStatusRequest $request, Booking $booking, BookingStateService $service, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $booking);

        try {
            $booking = $service->transition($booking, $request->status, auth()->user(), $request->note, true);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => new BookingResource($booking)]);
    }

    public function statusLogs(Booking $booking, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $booking);

        return BookingStatusLogResource::collection($booking->statusLogs()->with('changedBy')->latest()->get())
            ->additional(['success' => true]);
    }

    public function media(Booking $booking, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $booking);

        return BookingMediaResource::collection($booking->media()->with('uploadedBy')->latest()->get())
            ->additional(['success' => true]);
    }

    public function tracking(Booking $booking, LocationTrackingService $service, CityScopeService $cityScope)
    {
        $cityScope->ensureCanAccessModel(auth()->user(), $booking);

        return response()->json([
            'success' => true,
            'data' => $service->trackingSnapshot($booking),
        ]);
    }
}
