<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\PartnerJobService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BookingController extends Controller
{
    public function __construct(
        protected PartnerJobService $partnerJobService
    ) {}

    public function index(Request $request)
    {
        $request->validate([
            'filter' => 'nullable|in:today,upcoming,completed,all',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $bookings = $this->partnerJobService->paginatedJobs(
            auth()->id(),
            $request->query('filter') === 'all' ? null : $request->query('filter'),
            (int) $request->query('per_page', 15)
        );

        return response()->json(['success' => true, 'data' => $bookings]);
    }

    public function show(Booking $booking)
    {
        $this->authorizeJob($booking);

        $booking->load(['service', 'vehicle', 'user', 'images', 'statusHistories']);

        return response()->json(['success' => true, 'data' => $booking]);
    }

    public function uploadImage(Request $request, Booking $booking)
    {
        $this->authorizeJob($booking);

        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'image_type' => 'required|in:before,after',
        ]);

        try {
            $image = $this->partnerJobService->uploadImage(
                $booking,
                auth()->user(),
                $request->file('image'),
                $request->image_type
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->image_type) . ' image uploaded successfully.',
            'data' => $image,
        ]);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $this->authorizeJob($booking);

        $request->validate([
            'status' => 'required|in:accepted,on_the_way,started,completed',
        ]);

        try {
            $partner = auth()->user();
            $booking = match ($request->status) {
                'accepted' => $this->partnerJobService->acceptJob($booking, $partner),
                'on_the_way' => $this->partnerJobService->markOnTheWay($booking, $partner),
                'started' => $this->partnerJobService->startJob($booking, $partner),
                'completed' => $this->partnerJobService->completeJob($booking, $partner),
            };
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully.',
            'data' => $booking,
        ]);
    }

    public function collectCod(Booking $booking)
    {
        $this->authorizeJob($booking);

        try {
            $booking = $this->partnerJobService->collectCodPayment($booking, auth()->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'COD payment marked as collected.',
            'data' => $booking,
        ]);
    }

    public function earnings()
    {
        return response()->json([
            'success' => true,
            'data' => $this->partnerJobService->earningsSummary(auth()->id()),
        ]);
    }

    protected function authorizeJob(Booking $booking): void
    {
        if ($booking->partner_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}
