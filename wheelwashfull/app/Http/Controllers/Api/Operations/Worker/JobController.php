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
use App\Services\DistanceService;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class JobController extends Controller
{
    public function dashboard()
    {
        $jobs = $this->baseQuery();
        $todayJobs = (clone $jobs)->whereDate('booking_date', today())->count();
        $completedJobs = (clone $jobs)->where('status', BookingStatus::COMPLETED)->count();
        $pendingJobs = (clone $jobs)->whereNotIn('status', [BookingStatus::COMPLETED, BookingStatus::CANCELLED])->count();
        $activeJob = (clone $jobs)->whereNotIn('status', [BookingStatus::COMPLETED, BookingStatus::CANCELLED])->oldest('booking_date')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'jobs_today' => $todayJobs,
                'total_earnings' => (clone $jobs)->where('status', BookingStatus::COMPLETED)->sum('total_amount') * 0.1,
                'pending_jobs' => $pendingJobs,
                'completed_jobs' => $completedJobs,
                'is_online' => auth()->user()->workerProfile?->current_status === 'available',
                'active_job' => $activeJob ? new BookingResource($activeJob) : null,
            ],
        ]);
    }

    public function index(Request $request)
    {
        $query = $this->baseQuery()->with(['service', 'vehicle', 'user', 'pickupAddress', 'dropAddress']);

        match ($request->query('tab', 'today')) {
            'upcoming' => $query->whereDate('booking_date', '>', today())->whereNotIn('status', [BookingStatus::COMPLETED, BookingStatus::CANCELLED]),
            'completed' => $query->where('status', BookingStatus::COMPLETED),
            default => $query->whereDate('booking_date', today())->whereNotIn('status', [BookingStatus::COMPLETED, BookingStatus::CANCELLED]),
        };

        return BookingResource::collection($query->orderBy('slot_time')->paginate($request->query('per_page', 15)))
            ->additional(['success' => true]);
    }

    public function show(Booking $booking)
    {
        $this->authorizeJob($booking);

        return response()->json(['success' => true, 'data' => new BookingDetailResource($booking->load(['service', 'vehicle', 'user', 'media', 'statusLogs', 'pickupAddress', 'dropAddress']))]);
    }

    public function startTravel(Booking $booking, BookingStateService $state)
    {
        return $this->transition($booking, $state, BookingStatus::WORKER_ON_THE_WAY, 'Worker started travel');
    }

    public function arrived(Request $request, Booking $booking, BookingStateService $state, DistanceService $distance)
    {
        $this->validateArrival($request, $booking, $distance, 300);

        return $this->transition($booking, $state, BookingStatus::REACHED_LOCATION, 'Worker reached customer location');
    }

    public function startService(Booking $booking, BookingStateService $state, MediaUploadService $media)
    {
        $this->authorizeJob($booking);
        $media->assertCanStatus($booking, BookingStatus::SERVICE_STARTED);

        return $this->transition($booking, $state, BookingStatus::SERVICE_STARTED, 'Before images uploaded; service started');
    }

    public function completeService(Booking $booking, BookingStateService $state, MediaUploadService $media)
    {
        $this->authorizeJob($booking);
        $media->assertCanStatus($booking, BookingStatus::SERVICE_COMPLETED);

        return $this->transition($booking, $state, BookingStatus::SERVICE_COMPLETED, 'After images uploaded; service completed');
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
        abort_unless(in_array($request->status, [BookingStatus::WORKER_ON_THE_WAY, BookingStatus::REACHED_LOCATION, BookingStatus::SERVICE_STARTED, BookingStatus::SERVICE_COMPLETED, BookingStatus::COMPLETED], true), 422, 'Invalid worker action.');
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
            'amount' => $booking->total_amount ? round($booking->total_amount * 0.1, 2) : 0,
            'status' => 'completed',
            'date' => $booking->updated_at->toDateString(),
        ]);

        return response()->json(['success' => true, 'data' => ['total_earnings' => $transactions->sum('amount'), 'transactions' => $transactions]]);
    }

    public function profile()
    {
        return response()->json(['success' => true, 'data' => auth()->user()->load('workerProfile')]);
    }

    public function updateProfile(Request $request)
    {
        auth()->user()->workerProfile()->updateOrCreate(['user_id' => auth()->id()], $request->only(['service_area', 'service_radius', 'latitude', 'longitude']));

        return $this->profile();
    }

    protected function baseQuery()
    {
        return Booking::query()
            ->where('worker_id', auth()->id())
            ->where(function ($query) {
                $query->where('wash_type', 'door_to_door')
                    ->orWhere('service_mode', 'doorstep');
            });
    }

    protected function authorizeJob(Booking $booking): void
    {
        abort_unless((int) $booking->worker_id === (int) auth()->id(), 403);
        abort_unless($booking->wash_type === 'door_to_door' || $booking->service_mode === 'doorstep', 403, 'Workers can only handle door-to-door bookings.');
    }

    protected function transition(Booking $booking, BookingStateService $state, string $newStatus, ?string $note = null)
    {
        $this->authorizeJob($booking);

        try {
            $booking = $state->transition($booking, $newStatus, auth()->user(), $note);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => new BookingResource($booking->load(['service', 'vehicle', 'user']))]);
    }

    protected function validateArrival(Request $request, Booking $booking, DistanceService $distance, int $allowedMeters): void
    {
        $this->authorizeJob($booking);
        $data = $request->validate(['latitude' => ['required', 'numeric'], 'longitude' => ['required', 'numeric']]);
        $meters = $distance->haversineDistance((float) $data['latitude'], (float) $data['longitude'], (float) $booking->latitude, (float) $booking->longitude) * 1000;

        if ($meters > $allowedMeters) {
            abort(response()->json([
                'success' => false,
                'message' => 'You are too far from the destination to perform this action.',
                'distance_meters' => round($meters),
                'allowed_radius_meters' => $allowedMeters,
            ], 422));
        }
    }
}
