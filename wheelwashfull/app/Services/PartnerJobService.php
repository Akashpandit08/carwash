<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingImage;
use App\Models\BookingStatusHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PartnerJobService
{
    public const PARTNER_STATUSES = [
        'assigned',
        'accepted',
        'on_the_way',
        'started',
        'completed',
    ];

    public const TRANSITIONS = [
        'assigned' => 'accepted',
        'accepted' => 'on_the_way',
        'on_the_way' => 'started',
        'started' => 'completed',
    ];

    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function todayJobs(int $partnerId)
    {
        return $this->partnerJobsQuery($partnerId)
            ->whereDate('booking_date', Carbon::today())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('slot_time')
            ->get();
    }

    public function upcomingJobs(int $partnerId)
    {
        return $this->partnerJobsQuery($partnerId)
            ->whereDate('booking_date', '>', Carbon::today())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('booking_date')
            ->orderBy('slot_time')
            ->get();
    }

    public function completedJobs(int $partnerId)
    {
        return $this->partnerJobsQuery($partnerId)
            ->where('status', 'completed')
            ->orderByDesc('booking_date')
            ->orderByDesc('slot_time')
            ->get();
    }

    public function paginatedJobs(int $partnerId, ?string $filter = null, int $perPage = 15)
    {
        $query = $this->partnerJobsQuery($partnerId);

        if ($filter === 'today') {
            $query->whereDate('booking_date', Carbon::today())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->orderBy('slot_time');
        } elseif ($filter === 'upcoming') {
            $query->whereDate('booking_date', '>', Carbon::today())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->orderBy('booking_date')
                ->orderBy('slot_time');
        } elseif ($filter === 'completed') {
            $query->where('status', 'completed')
                ->orderByDesc('booking_date')
                ->orderByDesc('slot_time');
        } else {
            $query->orderBy('booking_date')
                ->orderBy('slot_time');
        }

        return $query->paginate(min(max($perPage, 1), 50));
    }

    public function earningsSummary(int $partnerId): array
    {
        $base = Booking::where('partner_id', $partnerId)->where('status', 'completed');

        return [
            'today' => (clone $base)->whereDate('booking_date', Carbon::today())->sum('final_price'),
            'week' => (clone $base)->whereBetween('booking_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])->sum('final_price'),
            'month' => (clone $base)->whereMonth('booking_date', Carbon::now()->month)
                ->whereYear('booking_date', Carbon::now()->year)
                ->sum('final_price'),
            'total' => (clone $base)->sum('final_price'),
            'jobs_completed' => (clone $base)->count(),
        ];
    }

    public function acceptJob(Booking $booking, User $partner): Booking
    {
        return $this->transition($booking, $partner, 'accepted', function () use ($booking) {
            if ($booking->user) {
                $this->notificationService->sendPartnerAccepted($booking->user, $booking);
            }
        });
    }

    public function markOnTheWay(Booking $booking, User $partner): Booking
    {
        return $this->transition($booking, $partner, 'on_the_way', function () use ($booking) {
            if ($booking->user) {
                $this->notificationService->sendPartnerOnTheWay($booking->user, $booking);
            }
        });
    }

    public function startJob(Booking $booking, User $partner): Booking
    {
        $hasBeforeImage = BookingImage::where('booking_id', $booking->id)
            ->where('image_type', 'before')
            ->exists();

        if (!$hasBeforeImage) {
            throw new InvalidArgumentException('Upload at least one before image before starting the job.');
        }

        return $this->transition($booking, $partner, 'started', function () use ($booking) {
            if ($booking->user) {
                $this->notificationService->sendJobStarted($booking->user, $booking);
            }
        });
    }

    public function collectCodPayment(Booking $booking, User $partner): Booking
    {
        $this->ensurePartnerOwnsJob($booking, $partner);

        if ($booking->payment_method !== 'cod') {
            throw new InvalidArgumentException('This booking is not a COD booking.');
        }

        if (!in_array($booking->status, ['started', 'completed'], true)) {
            throw new InvalidArgumentException('COD can be marked collected only after the job has started.');
        }

        if ($booking->payment_status === 'paid') {
            return $booking->fresh(['latestPayment']);
        }

        return DB::transaction(function () use ($booking, $partner) {
            $payment = $booking->latestPayment;

            if (!$payment) {
                $payment = $booking->payments()->create([
                    'payment_reference' => app(PaymentService::class)->generatePaymentReference(),
                    'method' => 'cod',
                    'status' => 'pending',
                    'amount' => $booking->final_price,
                    'currency' => 'INR',
                ]);
            }

            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'source' => 'partner_cod_collection',
                    'collected_by' => $partner->id,
                    'collected_at' => now()->toIso8601String(),
                ]),
            ]);

            $booking->update(['payment_status' => 'paid']);

            if ($booking->user) {
                $this->notificationService->sendPaymentSuccess($booking->user, $booking);
            }

            return $booking->fresh(['service', 'vehicle', 'user', 'images', 'latestPayment']);
        });
    }

    public function completeJob(Booking $booking, User $partner): Booking
    {
        $hasAfterImage = BookingImage::where('booking_id', $booking->id)
            ->where('image_type', 'after')
            ->exists();

        if (!$hasAfterImage) {
            throw new InvalidArgumentException('Upload at least one after image before completing the job.');
        }

        return $this->transition($booking, $partner, 'completed', function () use ($booking) {
            if ($booking->user) {
                $this->notificationService->sendJobCompleted($booking->user, $booking);
                $this->notificationService->sendReviewRequest($booking->user, $booking);
            }
        });
    }

    public function uploadImage(Booking $booking, User $partner, UploadedFile $file, string $imageType): BookingImage
    {
        $this->ensurePartnerOwnsJob($booking, $partner);

        if (!in_array($imageType, ['before', 'after'], true)) {
            throw new InvalidArgumentException('Invalid image type.');
        }

        $cloudinaryUrl = $file->storeOnCloudinary('booking_images')->getSecurePath();

        return BookingImage::create([
            'booking_id' => $booking->id,
            'image_type' => $imageType,
            'image_path' => $cloudinaryUrl,
            'uploaded_by' => $partner->id,
        ]);
    }

    public function recordStatusChange(
        Booking $booking,
        string $status,
        ?User $changedBy = null,
        ?string $role = null,
        ?string $notes = null
    ): BookingStatusHistory {
        return BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'status' => $status,
            'changed_by' => $changedBy?->id,
            'changed_by_role' => $role ?? $changedBy?->role,
            'notes' => $notes,
        ]);
    }

    protected function transition(Booking $booking, User $partner, string $toStatus, ?callable $after = null): Booking
    {
        $this->ensurePartnerOwnsJob($booking, $partner);

        $expectedFrom = array_search($toStatus, self::TRANSITIONS, true);

        if ($expectedFrom === false || $booking->status !== $expectedFrom) {
            throw new InvalidArgumentException("Cannot change status from {$booking->status} to {$toStatus}.");
        }

        return DB::transaction(function () use ($booking, $partner, $toStatus, $after) {
            $booking->update(['status' => $toStatus]);

            $this->recordStatusChange($booking, $toStatus, $partner, 'partner');

            if ($after) {
                $after();
            }

            return $booking->fresh(['service', 'vehicle', 'user', 'images', 'latestPayment']);
        });
    }

    protected function partnerJobsQuery(int $partnerId)
    {
        return Booking::with(['service', 'vehicle', 'user', 'latestPayment'])
            ->where('partner_id', $partnerId);
    }

    protected function ensurePartnerOwnsJob(Booking $booking, User $partner): void
    {
        if ($booking->partner_id !== $partner->id) {
            throw new InvalidArgumentException('This job is not assigned to you.');
        }
    }
}
