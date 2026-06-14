<?php

namespace App\Services;

use App\Constants\UserRole;
use App\Models\Booking;
use App\Models\AppNotification;
use App\Models\NotificationLog;
use App\Models\NotificationSound;
use App\Models\NotificationUser;
use App\Models\User;
use App\Models\UserDevice;
use App\Jobs\SendWhatsAppNotificationJob;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    private const ROLE_MAP = [
        'driver' => 'pickup_driver',
        'customer' => 'customer',
        'partner' => 'partner',
        'worker' => 'worker',
    ];

    // =========================================================================
    // OPERATIONAL PUSH NOTIFICATION METHODS (NEW)
    // =========================================================================

    /**
     * Send a push notification to a specific user by role.
     */
    public function sendPushToUser(
        int $userId,
        string $role,
        string $title,
        string $body,
        ?int $bookingId = null,
        string $type = 'general',
        array $data = [],
        ?string $soundName = null
    ): void {
        try {
            $data = array_merge([
                'event_type' => $type,
                'booking_id' => $bookingId,
                'role' => $role,
                'channelId' => 'default',
            ], $data);

            if ($this->recentDuplicateExists($userId, $type, $bookingId, $data['status'] ?? null)) {
                Log::info('Duplicate notification suppressed', [
                    'user_id' => $userId,
                    'event_type' => $type,
                    'booking_id' => $bookingId,
                ]);
                return;
            }

            $soundId = null;
            $soundFilePath = null;

            if ($soundName) {
                $sound = NotificationSound::where('name', $soundName)->first();
                $soundId = $sound?->id;
                $soundFilePath = $sound?->file_path;
            }

            if (! $soundFilePath) {
                $defaultSound = NotificationSound::where('is_default', true)->first();
                $soundId = $soundId ?? $defaultSound?->id;
                $soundFilePath = $defaultSound?->file_path;
            }

            // Create the notification record
            $notification = AppNotification::withoutGlobalScopes()->create([
                'title' => $title,
                'message' => $body,
                'body' => $body,
                'channel' => 'push',
                'type' => $type,
                'event_type' => $type,
                'user_id' => $userId,
                'target_role' => $role,
                'booking_id' => $bookingId,
                'sound_id' => $soundId,
                'screen' => $data['screen'] ?? null,
                'data' => $data,
                'status' => 'sent',
                'sent_at' => now('Asia/Kolkata'),
            ]);

            // Create recipient record
            NotificationUser::create([
                'notification_id' => $notification->id,
                'user_id' => $userId,
                'role' => $role,
                'status' => 'sent',
                'sent_at' => now('Asia/Kolkata'),
            ]);

            // Fetch active devices and send push
            $devices = UserDevice::where('user_id', $userId)
                ->where('is_active', true)
                ->get();
            $tokens = $devices
                ->map(fn (UserDevice $device) => $device->expo_push_token ?? $device->device_token)
                ->filter()
                ->values();

            Log::info('Notification devices selected', [
                'user_id' => $userId,
                'role' => $role,
                'notification_id' => $notification->id,
                'device_count' => $devices->count(),
                'expo_token_count' => $tokens->count(),
                'device_ids' => $devices->pluck('id')->values()->all(),
            ]);

            foreach ($devices as $device) {
                $token = $device->expo_push_token ?? $device->device_token;
                if ($token) {
                    $this->sendExpoPush($token, $title, $body, array_merge($data, [
                        'type' => $type,
                        'sound' => $soundFilePath,
                        'notification_id' => $notification->id,
                    ]), $device);
                }
            }
        } catch (Throwable $e) {
            Log::error('sendPushToUser failed', [
                'user_id' => $userId,
                'role' => $role,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendToUser(User|int $user, string $role, string $title, string $body, array $data = []): void
    {
        $recipient = $user instanceof User ? $user : User::find($user);
        if (! $recipient) {
            return;
        }

        $eventType = $data['event_type'] ?? $data['type'] ?? 'general';
        $bookingId = isset($data['booking_id']) ? (int) $data['booking_id'] : null;

        $this->sendPushToUser($recipient->id, $role ?: $recipient->role, $title, $body, $bookingId, $eventType, $data);
    }

    public function sendToUsers(Collection|SupportCollection|array $users, string $title, string $body, array $data = []): void
    {
        foreach ($users as $user) {
            if ($user instanceof User) {
                $this->sendToUser($user, $user->role, $title, $body, $data);
            } elseif (is_array($user) && isset($user['user_id'])) {
                $this->sendToUser((int) $user['user_id'], $user['role'] ?? '', $title, $body, $data);
            } elseif (is_numeric($user)) {
                $this->sendToUser((int) $user, '', $title, $body, $data);
            }
        }
    }

    public function notifyAdminsForBooking($booking, string $title, string $body, array $data = []): void
    {
        $query = User::whereIn('role', UserRole::ADMIN_ROLES);

        if ($booking?->service_city_id) {
            $query->where(function ($adminQuery) use ($booking) {
                $adminQuery
                    ->whereIn('role', [UserRole::ADMIN, UserRole::SUPER_ADMIN])
                    ->orWhere(function ($cityQuery) use ($booking) {
                        $cityQuery->where('role', UserRole::CITY_ADMIN)
                            ->where('service_city_id', $booking->service_city_id);
                    });
            });
        }

        $this->sendToUsers($query->get(), $title, $body, array_merge([
            'screen' => 'booking_detail',
            'booking_id' => $booking?->id,
            'booking_number' => $booking?->booking_number,
            'status' => $booking?->status,
        ], $data));
    }

    /**
     * Send a push notification to all users of a given role.
     */
    public function sendPushToRole(
        string $role,
        string $title,
        string $body,
        ?int $bookingId = null,
        string $type = 'general',
        array $data = [],
        ?string $soundName = null
    ): void {
        try {
            $users = $role === UserRole::ADMIN
                ? User::whereIn('role', UserRole::ADMIN_ROLES)->get()
                : User::where('role', $role)->get();
            foreach ($users as $user) {
                $this->sendPushToUser($user->id, $user->role, $title, $body, $bookingId, $type, $data, $soundName);
            }
        } catch (Throwable $e) {
            Log::error('sendPushToRole failed', [
                'role' => $role,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send push to multiple specific users (array of [user_id, role] pairs).
     */
    public function sendPushToUsers(
        array $userRolePairs,
        string $title,
        string $body,
        ?int $bookingId = null,
        string $type = 'general',
        array $data = [],
        ?string $soundName = null
    ): void {
        foreach ($userRolePairs as $pair) {
            $this->sendPushToUser(
                $pair['user_id'],
                $pair['role'],
                $title,
                $body,
                $bookingId,
                $type,
                $data,
                $soundName
            );
        }
    }

    /**
     * Send Expo push notification to a device token.
     */
    private function recentDuplicateExists(int $userId, string $eventType, ?int $bookingId, ?string $status = null): bool
    {
        return NotificationUser::where('user_id', $userId)
            ->whereHas('notification', function ($query) use ($eventType, $bookingId, $status) {
                $query->withoutGlobalScopes()
                    ->where(function ($notificationQuery) use ($eventType) {
                        $notificationQuery->where('event_type', $eventType)->orWhere('type', $eventType);
                    })
                    ->when($bookingId, fn ($notificationQuery) => $notificationQuery->where('booking_id', $bookingId))
                    ->when($status, fn ($notificationQuery) => $notificationQuery->where('data->status', $status))
                    ->where('created_at', '>=', now('Asia/Kolkata')->subMinutes(10));
            })
            ->exists();
    }

    private function dispatchExpoPush(string $deviceToken, string $title, string $body, array $data = []): void
    {
        try {
            $this->sendExpoPush($deviceToken, $title, $body, $data);
        } catch (Throwable $e) {
            Log::error('dispatchExpoPush exception', [
                'token' => substr($deviceToken, 0, 20) . '...',
                'error' => $e->getMessage(),
            ]);
        }
    }

    // =========================================================================
    // OPERATIONAL NOTIFICATION HOOKS (Enhanced from placeholders)
    // =========================================================================

    /**
     * Booking confirmed → notify customer
     */
    public function bookingConfirmed(Booking $booking): void
    {
        $booking->loadMissing('user');
        $customer = $booking->user;

        if ($customer) {
            $this->sendPushToUser($customer->id, 'customer', 'Booking Accepted', 'Your booking has been accepted.', $booking->id, 'booking_accepted', [
                'screen' => 'booking_tracking',
                'booking_id' => $booking->id,
                'status' => $booking->status,
            ]);
        }

        $this->notifyAdminsForBooking($booking, 'Booking Accepted', 'A booking has been accepted.', [
            'event_type' => 'booking_accepted',
            'screen' => 'booking_detail',
        ]);
    }

    /**
     * Worker assigned → notify worker + customer
     */
    public function workerAssigned(Booking $booking, User $worker): void
    {
        // Notify worker
        $this->sendPushToUser($worker->id, 'worker', 'New Job Assigned', 'A new washing job has been assigned to you.', $booking->id, 'worker_assigned', [
            'screen' => 'worker_job_detail',
            'booking_id' => $booking->id,
            'status' => $booking->status,
        ]);

        // Notify customer
        $booking->loadMissing('user');
        $customer = $booking->user;
        if ($customer) {
            $this->sendPushToUser($customer->id, 'customer', 'Worker Assigned', 'A worker has been assigned for your vehicle wash.', $booking->id, 'worker_assigned_customer', [
                'screen' => 'booking_tracking',
                'booking_id' => $booking->id,
                'status' => $booking->status,
            ]);
        }

        $this->notifyAdminsForBooking($booking, 'Worker Assigned', 'A worker has been assigned to a booking.', [
            'event_type' => 'worker_assigned',
            'screen' => 'booking_detail',
        ]);

        if ($booking->partner) {
            $this->sendPushToUser($booking->partner->id, 'partner', 'Worker Assigned', 'A worker has been assigned to your booking.', $booking->id, 'worker_assigned_partner', [
                'screen' => 'partner_booking_detail',
                'booking_id' => $booking->id,
                'status' => $booking->status,
            ]);
        }
    }

    /**
     * Partner assigned → notify partner + customer
     */
    public function partnerAssigned(Booking $booking, User $partner): void
    {
        // Notify partner
        $this->sendPushToUser($partner->id, 'partner', 'New Booking Assigned', 'A new booking has been assigned to your garage.', $booking->id, 'partner_assigned', [
            'screen' => 'partner_booking_detail',
            'booking_id' => $booking->id,
            'status' => $booking->status,
        ]);

        // Notify customer
        $booking->loadMissing('user');
        $customer = $booking->user;
        if ($customer) {
            $this->sendPushToUser($customer->id, 'customer', 'Partner Assigned', 'A service partner has been assigned for your booking.', $booking->id, 'partner_assigned_customer', [
                'screen' => 'booking_tracking',
                'booking_id' => $booking->id,
                'status' => $booking->status,
            ]);
        }

        $this->notifyAdminsForBooking($booking, 'Partner Assigned', 'A partner has been assigned to a booking.', [
            'event_type' => 'partner_assigned',
            'screen' => 'booking_detail',
        ]);
    }

    /**
     * Pickup driver assigned → notify driver + customer
     */
    public function pickupDriverAssigned(Booking $booking, User $driver): void
    {
        // Notify pickup driver
        $this->sendPushToUser($driver->id, 'pickup_driver', 'Pickup Assigned', 'You have a new vehicle pickup request.', $booking->id, 'pickup_assigned', [
            'screen' => 'driver_job_detail',
            'booking_id' => $booking->id,
            'status' => $booking->status,
        ]);

        // Notify customer
        $booking->loadMissing('user');
        $customer = $booking->user;
        if ($customer) {
            $this->sendPushToUser($customer->id, 'customer', 'Pickup Driver Assigned', 'A pickup driver has been assigned for your vehicle.', $booking->id, 'pickup_driver_assigned', [
                'screen' => 'booking_tracking',
                'booking_id' => $booking->id,
                'status' => $booking->status,
            ]);
        }

        $this->notifyAdminsForBooking($booking, 'Pickup Driver Assigned', 'A pickup driver has been assigned to a booking.', [
            'event_type' => 'pickup_driver_assigned',
            'screen' => 'booking_detail',
        ]);
    }

    /**
     * Status changed → full 15-event notification map.
     * Called from BookingStateService::transition().
     */
    public function statusChanged(Booking $booking, ?string $oldStatus, string $newStatus, ?User $actor = null): void
    {
        $bNum = $booking->booking_number ?? $booking->id;
        $booking->loadMissing(['user', 'partner', 'worker', 'pickupDriver', 'deliveryDriver']);
        $customer = $booking->user;

        switch ($newStatus) {
            // 5. Driver starts pickup
            case \App\Constants\BookingStatus::DRIVER_ON_THE_WAY:
                if ($customer) {
                    $this->sendPushToUser($customer->id, 'customer', 'Driver Started Pickup', 'Your driver is on the way to pick up your vehicle.', $booking->id, 'pickup_started', [
                        'screen' => 'booking_tracking',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                }
                // Also send WhatsApp
                if ($customer) {
                    $msg = "WashMate Update: Your pickup driver is on the way for booking ({$bNum}).";
                    $this->logAndQueue($customer, 'driver_on_the_way', $msg, $booking->id);
                }
                break;

            // 6. Vehicle picked up
            case \App\Constants\BookingStatus::CAR_PICKED_UP:
                if ($customer) {
                    $this->sendPushToUser($customer->id, 'customer', 'Vehicle Picked Up', 'Your vehicle has been picked up successfully.', $booking->id, 'vehicle_picked_up', [
                        'screen' => 'booking_tracking',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                    $msg = "WashMate Update: Your car has been picked up securely for booking ({$bNum}).";
                    $this->logAndQueue($customer, 'car_picked_up', $msg, $booking->id);
                }
                // Notify partner
                if ($booking->partner) {
                    $this->sendPushToUser($booking->partner->id, 'partner', 'Vehicle Picked Up', 'Driver has picked up the customer vehicle.', $booking->id, 'partner_vehicle_picked_up', [
                        'screen' => 'partner_booking_detail',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                }
                break;

            // 7. Vehicle reached garage
            case \App\Constants\BookingStatus::REACHED_PARTNER:
                // Notify partner + worker
                if ($booking->partner) {
                    $this->sendPushToUser($booking->partner->id, 'partner', 'Vehicle Reached Garage', 'Vehicle has reached the garage for washing.', $booking->id, 'vehicle_reached_garage', [
                        'screen' => 'partner_booking_detail',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                }
                if ($booking->worker) {
                    $this->sendPushToUser($booking->worker->id, 'worker', 'Vehicle Reached Garage', 'Vehicle has reached the garage for washing.', $booking->id, 'vehicle_reached_garage', [
                        'screen' => 'worker_job_detail',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                }
                if ($customer) {
                    $msg = "WashMate Update: Your car has reached our partner center for booking ({$bNum}).";
                    $this->logAndQueue($customer, 'reached_partner', $msg, $booking->id);
                }
                break;

            // 8. Worker starts washing
            case \App\Constants\BookingStatus::SERVICE_STARTED:
                if ($customer) {
                    $this->sendPushToUser($customer->id, 'customer', 'Wash Started', 'Your vehicle wash has started.', $booking->id, 'wash_started', [
                        'screen' => 'booking_tracking',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                }
                if ($booking->partner) {
                    $this->sendPushToUser($booking->partner->id, 'partner', 'Wash Started', 'Vehicle wash has started.', $booking->id, 'wash_started_partner', [
                        'screen' => 'partner_booking_detail',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                }
                $this->notifyAdminsForBooking($booking, 'Wash Started', 'Vehicle wash has started.', [
                    'event_type' => 'wash_started',
                    'status' => $newStatus,
                ]);
                break;

            // 10. Worker completes wash
            case \App\Constants\BookingStatus::SERVICE_COMPLETED:
                if ($customer) {
                    $this->sendPushToUser($customer->id, 'customer', 'Wash Completed', 'Your vehicle wash has been completed.', $booking->id, 'wash_completed', [
                        'screen' => 'booking_tracking',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                }
                if ($booking->partner) {
                    $this->sendPushToUser($booking->partner->id, 'partner', 'Wash Completed', 'Vehicle wash has been completed.', $booking->id, 'wash_completed_partner', [
                        'screen' => 'partner_booking_detail',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                }
                $this->notifyAdminsForBooking($booking, 'Wash Completed', 'Vehicle wash has been completed.', [
                    'event_type' => 'wash_completed',
                    'status' => $newStatus,
                ]);
                // Notify pickup driver that vehicle is ready
                if ($booking->pickupDriver) {
                    $this->sendPushToUser($booking->pickupDriver->id, 'pickup_driver', 'Vehicle Ready for Delivery', 'Vehicle is ready to be delivered to the customer.', $booking->id, 'ready_for_delivery', [
                        'screen' => 'driver_job_detail',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                }
                break;

            // 11. Driver starts delivery
            case \App\Constants\BookingStatus::OUT_FOR_DELIVERY:
                if ($customer) {
                    $this->sendPushToUser($customer->id, 'customer', 'Vehicle Out for Delivery', 'Your vehicle is on the way back to you.', $booking->id, 'delivery_started', [
                        'screen' => 'booking_tracking',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                    $msg = "WashMate Update: Great news! Your car is clean and out for delivery for booking ({$bNum}).";
                    $this->logAndQueue($customer, 'out_for_delivery', $msg, $booking->id);
                }
                break;

            // 12. Vehicle delivered
            case \App\Constants\BookingStatus::DELIVERED:
                if ($customer) {
                    $this->sendPushToUser($customer->id, 'customer', 'Vehicle Delivered', 'Your vehicle has been delivered successfully.', $booking->id, 'vehicle_delivered', [
                        'screen' => 'booking_detail',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                    $msg = "WashMate Update: Your car has been delivered successfully. Thank you for using WashMate!";
                    $this->logAndQueue($customer, 'delivered', $msg, $booking->id);
                }
                // Notify partner/admin
                if ($booking->partner) {
                    $this->sendPushToUser($booking->partner->id, 'partner', 'Booking Delivered', 'Vehicle delivery has been completed.', $booking->id, 'booking_delivered', [
                        'screen' => 'partner_booking_detail',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                }
                $this->notifyAdminsForBooking($booking, 'Booking Delivered', 'Vehicle delivery has been completed.', [
                    'event_type' => 'vehicle_delivered',
                    'screen' => 'booking_detail',
                    'status' => $newStatus,
                ]);
                break;

            // Worker on the way (doorstep mode)
            case \App\Constants\BookingStatus::WORKER_ON_THE_WAY:
                if ($customer) {
                    $this->sendPushToUser($customer->id, 'customer', 'Worker On The Way', 'Your worker is on the way to your location.', $booking->id, 'worker_on_the_way', [
                        'screen' => 'booking_tracking',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                }
                break;

            // Completed (final state)
            case \App\Constants\BookingStatus::COMPLETED:
                if ($customer) {
                    $this->sendPushToUser($customer->id, 'customer', 'Booking Completed', 'Your booking has been completed. Thank you for using WashMate!', $booking->id, 'booking_completed', [
                        'screen' => 'booking_detail',
                        'booking_id' => $booking->id,
                        'status' => $newStatus,
                    ]);
                }
                break;

            // 15. Booking cancelled
            case \App\Constants\BookingStatus::CANCELLED:
                $this->notifyBookingCancelled($booking);
                break;
        }
    }

    /**
     * Booking created → notify partner/admin
     */
    public function notifyBookingCreatedPush(Booking $booking): void
    {
        $booking->loadMissing('user');

        if ($booking->user) {
            $this->sendPushToUser($booking->user->id, 'customer', 'Booking Created', 'Your booking has been created successfully.', $booking->id, 'booking_created', [
                'screen' => 'booking_detail',
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'status' => $booking->status,
            ]);
        }

        // Notify all admins
        $this->notifyAdminsForBooking($booking, 'New Booking Received', 'A new customer booking has been created.', [
            'event_type' => 'booking_created',
            'screen' => 'booking_detail',
            'status' => $booking->status,
        ]);

        // Notify assigned partner if any
        if ($booking->partner_id) {
            $this->sendPushToUser($booking->partner_id, 'partner', 'New Booking Received', 'A new customer booking has been created.', $booking->id, 'booking_created', [
                'screen' => 'partner_booking_detail',
                'booking_id' => $booking->id,
                'status' => $booking->status,
            ], 'booking_alert');
        }
    }

    /**
     * Payment completed → notify partner/admin
     */
    public function notifyPaymentCompletedPush(Booking $booking): void
    {
        $booking->loadMissing('user');

        // Notify customer
        if ($booking->user) {
            $this->sendPushToUser($booking->user->id, 'customer', 'Payment Successful', 'Your payment has been completed successfully.', $booking->id, 'payment_completed', [
                'screen' => 'booking_detail',
                'booking_id' => $booking->id,
                'status' => $booking->status,
            ]);
        }

        // Notify partner
        if ($booking->partner_id) {
            $this->sendPushToUser($booking->partner_id, 'partner', 'Payment Received', 'Payment has been received for a booking.', $booking->id, 'payment_received', [
                'screen' => 'partner_booking_detail',
                'booking_id' => $booking->id,
                'status' => $booking->status,
            ]);
        }

        // Notify admin
        $this->notifyAdminsForBooking($booking, 'Payment Received', 'Payment has been received for a booking.', [
            'event_type' => 'payment_completed',
            'screen' => 'booking_detail',
            'status' => $booking->status,
        ]);
    }

    /**
     * Booking cancelled → notify all involved parties
     */
    public function notifyBookingCancelled(Booking $booking): void
    {
        $booking->loadMissing(['user', 'partner', 'worker', 'pickupDriver', 'deliveryDriver']);

        $title = 'Booking Cancelled';
        $body = 'This booking has been cancelled.';
        $type = 'booking_cancelled';
        $data = [
            'screen' => 'booking_detail',
            'booking_id' => $booking->id,
            'status' => 'cancelled',
        ];

        // Notify customer
        if ($booking->user) {
            $this->sendPushToUser($booking->user->id, 'customer', $title, $body, $booking->id, $type, $data);
        }

        // Notify partner
        if ($booking->partner) {
            $this->sendPushToUser($booking->partner->id, 'partner', $title, $body, $booking->id, $type, array_merge($data, ['screen' => 'partner_booking_detail']));
        }

        // Notify worker
        if ($booking->worker) {
            $this->sendPushToUser($booking->worker->id, 'worker', $title, $body, $booking->id, $type, array_merge($data, ['screen' => 'worker_job_detail']));
        }

        // Notify pickup driver
        if ($booking->pickupDriver) {
            $this->sendPushToUser($booking->pickupDriver->id, 'pickup_driver', $title, $body, $booking->id, $type, array_merge($data, ['screen' => 'driver_job_detail']));
        }

        $this->notifyAdminsForBooking($booking, $title, $body, array_merge($data, [
            'event_type' => $type,
            'screen' => 'booking_detail',
        ]));
    }

    /**
     * Worker uploads proof/images → notify customer + partner (event 9)
     */
    public function notifyProofUploaded(Booking $booking): void
    {
        $booking->loadMissing(['user', 'partner']);

        if ($booking->user) {
            $this->sendPushToUser($booking->user->id, 'customer', 'Wash Proof Uploaded', 'Vehicle wash proof/images have been uploaded.', $booking->id, 'proof_uploaded', [
                'screen' => 'booking_detail',
                'booking_id' => $booking->id,
                'status' => $booking->status,
            ]);
        }

        if ($booking->partner) {
            $this->sendPushToUser($booking->partner->id, 'partner', 'Wash Proof Uploaded', 'Vehicle wash proof/images have been uploaded.', $booking->id, 'proof_uploaded', [
                'screen' => 'partner_booking_detail',
                'booking_id' => $booking->id,
                'status' => $booking->status,
            ]);
        }

        $this->notifyAdminsForBooking($booking, 'Wash Proof Uploaded', 'Vehicle wash proof/images have been uploaded.', [
            'event_type' => 'proof_uploaded',
            'screen' => 'booking_detail',
            'status' => $booking->status,
        ]);
    }

    // =========================================================================
    // EXISTING WHATSAPP NOTIFICATION METHODS (preserved)
    // =========================================================================

    private function logAndQueue(User $user, string $type, string $message, ?int $bookingId = null)
    {
        // Existing log & queue (preserves old DB record behavior)
        $notification = NotificationLog::create([
            'user_id' => $user->id,
            'booking_id' => $bookingId,
            'type' => $type,
            'channel' => 'whatsapp',
            'message' => $message,
            'status' => 'pending',
        ]);

        SendWhatsAppNotificationJob::dispatch($notification->id);

        // Fail-safe Omneaxa WhatsApp integration
        $templateMap = [
            'otp' => 'otp_login',
            'booking_created' => 'booking_created',
            'partner_assigned_partner' => 'worker_assigned',
            'partner_assigned_customer' => 'worker_assigned',
            'partner_accepted' => 'booking_accepted',
            'partner_on_the_way' => 'pickup_started',
            'driver_on_the_way' => 'pickup_started',
            'car_picked_up' => 'vehicle_picked_up',
            'reached_partner' => 'vehicle_reached_garage',
            'job_started' => 'wash_started',
            'proof_uploaded' => 'proof_uploaded',
            'job_completed' => 'wash_completed',
            'out_for_delivery' => 'delivery_started',
            'delivered' => 'vehicle_delivered',
            'payment_success' => 'payment_completed',
            'booking_cancelled_customer' => 'booking_cancelled',
            'booking_cancelled_partner' => 'booking_cancelled',
        ];

        $template = $templateMap[$type] ?? $type;

        app(\App\Services\OmneaxaWhatsAppService::class)->sendEvent(
            $user->mobile_number ?? '',
            $template,
            ['message' => $message],
            [
                'event_type' => $type,
                'module' => 'booking',
                'user_id' => $user->id,
                'role' => $user->role,
                'booking_id' => $bookingId,
            ]
        );
    }

    // 1. OTP login
    public function sendOtp(User $user, string $otp)
    {
        $msg = "WashMate Login: Your OTP is {$otp}. Do not share this with anyone.";
        $this->logAndQueue($user, 'otp', $msg);
    }

    // 2. Booking created
    public function sendBookingCreated(User $customer, Booking $booking)
    {
        $bNum = $booking->booking_number ?? $booking->id;
        $msg = "Hello {$customer->name},\nYour WashMate booking ({$bNum}) has been successfully created for {$booking->booking_date->format('M d')} at " . date('h:i A', strtotime($booking->slot_time)) . ".\nThank you for choosing WashMate!";
        $this->logAndQueue($customer, 'booking_created', $msg, $booking->id);

        // Also send push notifications to partner/admin
        $this->notifyBookingCreatedPush($booking);
    }

    // 3. Partner assigned
    public function sendPartnerAssigned(User $partner, User $customer, Booking $booking)
    {
        $bNum = $booking->booking_number ?? $booking->id;
        
        // To Partner
        $pMsg = "WashMate Alert: You have been assigned to booking {$bNum}.\nDate: {$booking->booking_date->format('M d')}\nTime: " . date('h:i A', strtotime($booking->slot_time)) . "\nAddress: {$booking->address}\nPlease check your partner app.";
        $this->logAndQueue($partner, 'partner_assigned_partner', $pMsg, $booking->id);

        // To Customer
        $cMsg = "Hi {$customer->name}, your WashMate partner, {$partner->name} ({$partner->mobile_number}), has been assigned to your booking ({$bNum}).";
        $this->logAndQueue($customer, 'partner_assigned_customer', $cMsg, $booking->id);
    }

    // 4. Partner accepted job
    public function sendPartnerAccepted(User $customer, Booking $booking)
    {
        $bNum = $booking->booking_number ?? $booking->id;
        $msg = "WashMate Update: Your partner has accepted booking ({$bNum}) and is preparing for the job.";
        $this->logAndQueue($customer, 'partner_accepted', $msg, $booking->id);
    }

    // 5. Partner on the way
    public function sendPartnerOnTheWay(User $customer, Booking $booking)
    {
        $bNum = $booking->booking_number ?? $booking->id;
        $msg = "WashMate Update: Great news! Your partner is on the way to your location for booking ({$bNum}).";
        $this->logAndQueue($customer, 'partner_on_the_way', $msg, $booking->id);
    }

    // 6. Job started
    public function sendJobStarted(User $customer, Booking $booking)
    {
        $bNum = $booking->booking_number ?? $booking->id;
        $msg = "WashMate Update: The washing service has started for your booking ({$bNum}). We'll notify you once it is completed!";
        $this->logAndQueue($customer, 'job_started', $msg, $booking->id);
    }

    // 7. Job completed
    public function sendJobCompleted(User $customer, Booking $booking)
    {
        $bNum = $booking->booking_number ?? $booking->id;
        $msg = "WashMate Update: Your vehicle wash for booking ({$bNum}) has been completed successfully! Thank you for using WashMate.";
        $this->logAndQueue($customer, 'job_completed', $msg, $booking->id);
    }

    // 8. Payment success
    public function sendPaymentSuccess(User $customer, Booking $booking)
    {
        $bNum = $booking->booking_number ?? $booking->id;
        $msg = "WashMate Payment: We have received your payment of ₹" . number_format($booking->final_price, 2) . " for booking ({$bNum}).";
        $this->logAndQueue($customer, 'payment_success', $msg, $booking->id);

        // Also send push notifications
        $this->notifyPaymentCompletedPush($booking);
    }

    // 9. Booking cancelled
    public function sendBookingCancelled(User $user, Booking $booking, bool $isPartner = false)
    {
        $bNum = $booking->booking_number ?? $booking->id;
        $target = $isPartner ? 'partner' : 'customer';
        $msg = "WashMate Alert: Booking ({$bNum}) has been cancelled.";
        $this->logAndQueue($user, 'booking_cancelled_' . $target, $msg, $booking->id);
    }

    // 10. Review request
    public function sendReviewRequest(User $customer, Booking $booking)
    {
        $bNum = $booking->booking_number ?? $booking->id;
        $msg = "WashMate: How did we do? Please rate your experience for booking ({$bNum}) on the WashMate app! Your feedback helps us improve.";
        $this->logAndQueue($customer, 'review_request', $msg, $booking->id);
    }

    public function payoutCreated(Booking $booking): void
    {
        Log::info('Payout created notification placeholder', ['booking_id' => $booking->id]);
    }

    // =========================================================================
    // CAMPAIGN / ADMIN NOTIFICATION METHODS (preserved)
    // =========================================================================

    public function createNotification(array $data, array $selectedUserIds = []): AppNotification
    {
        return DB::transaction(function () use ($data, $selectedUserIds) {
            $notification = AppNotification::create($data);
            $users = $this->resolveTargetUsers($notification, $selectedUserIds);

            foreach ($users as $user) {
                NotificationUser::firstOrCreate([
                    'notification_id' => $notification->id,
                    'user_id' => $user->id,
                ], ['status' => 'pending']);
            }

            return $notification->load('recipients.user');
        });
    }

    public function sendNow(AppNotification $notification): AppNotification
    {
        $notification->status = 'sent';
        $notification->sent_at = now();
        $failed = 0;

        foreach ($notification->recipients()->with('user.devices')->get() as $recipient) {
            if (! $this->sendCampaignToUser($notification, $recipient->user, $recipient)) {
                $failed++;
            }
        }

        if ($failed > 0 && $failed === $notification->recipients()->count()) {
            $notification->status = 'failed';
            $notification->error_message = 'All recipient sends failed.';
        }

        $notification->save();

        return $notification->refresh();
    }

    public function scheduleNotification(AppNotification $notification): AppNotification
    {
        $notification->update(['status' => 'scheduled']);

        return $notification;
    }

    public function sendCampaignToUser(AppNotification $notification, User $user, ?NotificationUser $recipient = null): bool
    {
        $recipient ??= NotificationUser::firstOrCreate([
            'notification_id' => $notification->id,
            'user_id' => $user->id,
        ]);

        try {
            $devices = $user->devices()->where('is_active', true)->get();

            if ($devices->isEmpty()) {
                throw new \RuntimeException('No active device token found.');
            }

            foreach ($devices as $device) {
                if ($device->expo_push_token) {
                    $this->sendExpoPush($device->expo_push_token, $notification);
                }
                if ($device->fcm_token) {
                    $this->sendFirebasePush($device->fcm_token, $notification);
                }
            }

            $recipient->update(['status' => 'sent', 'sent_at' => now(), 'error_message' => null]);

            return true;
        } catch (Throwable $e) {
            $recipient->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            Log::warning('Push notification failed', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendExpoPush(string $token, AppNotification|string $notificationOrTitle, ?string $body = null, array $data = [], ?UserDevice $device = null): void
    {
        if ($notificationOrTitle instanceof AppNotification) {
            $title = $notificationOrTitle->title;
            $body = $notificationOrTitle->message;
            $data = [
                'redirect_type' => $notificationOrTitle->redirect_type,
                'redirect_value' => $notificationOrTitle->redirect_value,
                'notification_id' => $notificationOrTitle->id,
            ];
        } else {
            $title = $notificationOrTitle;
            $body ??= '';
        }

        $response = Http::timeout(10)->post('https://exp.host/--/api/v2/push/send', [
            'to' => $token,
            'title' => $title,
            'body' => $body,
            'sound' => 'default',
            'channelId' => $data['channelId'] ?? 'default',
            'priority' => 'high',
            'data' => $data,
        ]);
        $responseData = $response->json();

        if (! $response->successful()) {
            Log::warning('Expo push failed', [
                'token' => substr($token, 0, 20) . '...',
                'device_id' => $device?->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Expo push failed: '.$response->body());
        }

        $error = $responseData['data']['details']['error'] ?? $responseData['errors'][0]['code'] ?? null;
        if ($error === 'DeviceNotRegistered' && $device) {
            $device->update(['is_active' => false]);
            Log::warning('Expo token marked inactive', [
                'device_id' => $device->id,
                'user_id' => $device->user_id,
                'error' => $error,
            ]);
        }

        Log::info('Expo push sent', [
            'token' => substr($token, 0, 20) . '...',
            'device_id' => $device?->id,
            'status' => $response->status(),
            'body' => $responseData ?: $response->body(),
        ]);
    }

    public function sendFirebasePush(string $token, AppNotification $notification): void
    {
        $serverKey = config('services.firebase.server_key');

        if (! $serverKey) {
            throw new \RuntimeException('Firebase server key is not configured.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'key='.$serverKey,
            'Content-Type' => 'application/json',
        ])->timeout(10)->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $token,
            'notification' => [
                'title' => $notification->title,
                'body' => $notification->message,
                'image' => $notification->image_url,
            ],
            'data' => [
                'redirect_type' => $notification->redirect_type,
                'redirect_value' => $notification->redirect_value,
                'notification_id' => (string) $notification->id,
            ],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Firebase push failed: '.$response->body());
        }
    }

    public function processScheduledNotifications(): int
    {
        $count = 0;

        AppNotification::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->with('recipients.user.devices')
            ->chunkById(50, function ($notifications) use (&$count) {
                foreach ($notifications as $notification) {
                    $this->sendNow($notification);
                    $count++;
                }
            });

        return $count;
    }

    private function resolveTargetUsers(AppNotification $notification, array $selectedUserIds = []): Collection
    {
        $query = User::query()->where('role', '!=', 'admin');

        if ($notification->target_type === 'selected_users') {
            return $query->whereIn('id', $selectedUserIds)->get();
        }

        if ($notification->target_type !== 'all') {
            $query->where('role', self::ROLE_MAP[$notification->target_type] ?? $notification->target_type);
        }

        return $query->get();
    }
}
