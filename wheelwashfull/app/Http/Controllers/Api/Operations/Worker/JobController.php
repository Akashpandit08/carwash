<?php

namespace App\Http\Controllers\Api\Operations\Worker;

use App\Constants\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateBookingStatusRequest;
use App\Http\Requests\Api\UploadBookingMediaRequest;
use App\Http\Resources\Api\BookingDetailResource;
use App\Http\Resources\Api\BookingMediaResource;
use App\Http\Resources\Api\BookingResource;
use App\Models\Booking;
use App\Services\BookingStateService;
use App\Services\MediaUploadService;
use InvalidArgumentException;

class JobController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'active_jobs' => Booking::where('worker_id', auth()->id())->whereNotIn('status', [BookingStatus::COMPLETED, BookingStatus::CANCELLED])->count(),
                'completed_jobs' => Booking::where('worker_id', auth()->id())->where('status', BookingStatus::COMPLETED)->count(),
            ],
        ]);
    }

    public function index()
    {
        return BookingResource::collection(Booking::with(['service', 'vehicle', 'user'])->where('worker_id', auth()->id())->latest()->paginate(request('per_page', 15)))
            ->additional(['success' => true]);
    }

    public function show(Booking $booking)
    {
        $this->authorizeJob($booking);

        return response()->json(['success' => true, 'data' => new BookingDetailResource($booking->load(['service', 'vehicle', 'user', 'media', 'statusLogs']))]);
    }

    public function status(UpdateBookingStatusRequest $request, Booking $booking, BookingStateService $state, MediaUploadService $media)
    {
        $this->authorizeJob($booking);
        abort_unless(in_array($request->status, [BookingStatus::WORKER_ON_THE_WAY, BookingStatus::SERVICE_STARTED, BookingStatus::SERVICE_COMPLETED, BookingStatus::COMPLETED], true), 422, 'Invalid worker action.');
        $media->assertCanStatus($booking, $request->status);

        try {
            $booking = $state->transition($booking, $request->status, auth()->user(), $request->note);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => new BookingResource($booking)]);
    }

    public function media(UploadBookingMediaRequest $request, Booking $booking, MediaUploadService $service)
    {
        $this->authorizeJob($booking);
        $media = $service->upload($booking, auth()->user(), $request->file('file'), $request->type);

        return response()->json(['success' => true, 'data' => new BookingMediaResource($media)], 201);
    }

    protected function authorizeJob(Booking $booking): void
    {
        abort_unless((int) $booking->worker_id === (int) auth()->id(), 403);
    }

    public function earnings()
    {
        // Simple mock earnings calculation using completed bookings
        $completedBookings = Booking::where('worker_id', auth()->id())
            ->where('status', BookingStatus::COMPLETED)
            ->get();

        $transactions = $completedBookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'booking_id' => $booking->id,
                // If there's no worker commission field, use a fixed percentage or 0 for MVP
                'amount' => $booking->total_amount ? round($booking->total_amount * 0.1, 2) : 0, 
                'status' => 'completed',
                'date' => $booking->updated_at->toDateString(),
            ];
        });

        $totalEarnings = $transactions->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total_earnings' => $totalEarnings,
                'transactions' => $transactions,
            ],
        ]);
    }
}
