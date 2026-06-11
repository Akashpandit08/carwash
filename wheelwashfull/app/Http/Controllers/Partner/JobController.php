<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\PartnerJobService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class JobController extends Controller
{
    public function __construct(
        protected PartnerJobService $partnerJobService
    ) {}

    public function today()
    {
        $jobs = $this->partnerJobService->todayJobs(auth()->id());

        return view('partner.jobs.today', compact('jobs'));
    }

    public function upcoming()
    {
        $jobs = $this->partnerJobService->upcomingJobs(auth()->id());

        return view('partner.jobs.upcoming', compact('jobs'));
    }

    public function show(Booking $booking)
    {
        $this->authorizeJob($booking);

        $booking->load(['service', 'vehicle', 'user', 'images', 'statusHistories.changedByUser']);

        return view('partner.jobs.show', compact('booking'));
    }

    public function accept(Booking $booking)
    {
        return $this->handleAction($booking, fn () => $this->partnerJobService->acceptJob($booking, auth()->user()), 'Job accepted.');
    }

    public function onTheWay(Booking $booking)
    {
        return $this->handleAction($booking, fn () => $this->partnerJobService->markOnTheWay($booking, auth()->user()), 'Marked as on the way.');
    }

    public function start(Booking $booking)
    {
        return $this->handleAction($booking, fn () => $this->partnerJobService->startJob($booking, auth()->user()), 'Job started.');
    }

    public function complete(Booking $booking)
    {
        return $this->handleAction($booking, fn () => $this->partnerJobService->completeJob($booking, auth()->user()), 'Job completed successfully.');
    }

    public function collectCod(Booking $booking)
    {
        return $this->handleAction($booking, fn () => $this->partnerJobService->collectCodPayment($booking, auth()->user()), 'COD payment marked as collected.');
    }

    public function uploadImage(Request $request, Booking $booking)
    {
        $this->authorizeJob($booking);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'image_type' => 'required|in:before,after',
        ]);

        try {
            $this->partnerJobService->uploadImage(
                $booking,
                auth()->user(),
                $request->file('image'),
                $request->image_type
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', ucfirst($request->image_type) . ' image uploaded.');
    }

    protected function handleAction(Booking $booking, callable $action, string $successMessage)
    {
        $this->authorizeJob($booking);

        try {
            $action();
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $successMessage);
    }

    protected function authorizeJob(Booking $booking): void
    {
        if ($booking->partner_id !== auth()->id()) {
            abort(403);
        }
    }
}
