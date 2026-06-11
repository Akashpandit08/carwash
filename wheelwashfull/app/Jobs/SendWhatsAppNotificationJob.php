<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\WhatsAppService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notificationId;

    /**
     * Create a new job instance.
     */
    public function __construct($notificationId)
    {
        $this->notificationId = $notificationId;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsAppService): void
    {
        $notification = NotificationLog::with('user')->find($this->notificationId);

        if (!$notification || $notification->status === 'sent') {
            return;
        }

        try {
            $mobile = $notification->user->mobile_number;

            $whatsAppService->sendMessage($mobile, $notification->message);

            $notification->update([
                'status' => 'sent',
                'error_message' => null,
                'last_error' => null,
            ]);
        } catch (Exception $e) {
            $notification->update([
                'status' => 'failed',
                'error_message' => substr($e->getMessage(), 0, 255),
                'last_error' => $e->getMessage(),
                'retry_count' => $notification->retry_count + 1,
            ]);
        }
    }
}
