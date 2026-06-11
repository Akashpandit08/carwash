<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendScheduledNotifications extends Command
{
    protected $signature = 'notifications:send-scheduled';

    protected $description = 'Send scheduled app push notifications that are due.';

    public function handle(NotificationService $notifications): int
    {
        $count = $notifications->processScheduledNotifications();
        $this->info("Processed {$count} scheduled notification(s).");

        return self::SUCCESS;
    }
}
