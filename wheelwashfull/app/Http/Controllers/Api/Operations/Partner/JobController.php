<?php

namespace App\Http\Controllers\Api\Operations\Partner;

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
                'active_jobs' => Booking::where('partner_id', auth()->id())->whereNotIn('status', [BookingStatus::COMPLETED, BookingStatus::CANCELLED])->count(),
                'completed_jobs' => Booking::where('partner_id', auth()->id())->where('status', BookingStatus::COMPLETED)->count(),
            ],
        ]);
    }

    public function index()
    {
        return BookingResource::collection(Booking::with(['service', 'vehicle', 'user'])->where('partner_id', auth()->id())->latest()->paginate(request('per_page', 15)))
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
        abort_unless(in_array($request->status, [BookingStatus::SERVICE_STARTED, BookingStatus::SERVICE_COMPLETED], true), 422, 'Invalid partner action.');
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
        abort_unless((int) $booking->partner_id === (int) auth()->id(), 403);
    }

    public function assignWorker(\Illuminate\Http\Request $request, Booking $booking, BookingStateService $state)
    {
        $this->authorizeJob($booking);

        $request->validate([
            'worker_id' => 'required|exists:users,id',
        ]);

        $worker = \App\Models\User::findOrFail($request->worker_id);
        abort_unless($worker->role === \App\Constants\UserRole::WORKER, 422, 'Selected user is not a worker.');

        $booking->worker_id = $worker->id;
        $booking->save();

        try {
            $booking = $state->transition($booking, BookingStatus::WORKER_ASSIGNED, auth()->user(), 'Worker assigned by partner');
        } catch (\Exception $e) {
            // Already saved worker_id, just swallow transition error if state is invalid for transition
        }

        return response()->json(['success' => true, 'data' => new BookingResource($booking)]);
    }
}
