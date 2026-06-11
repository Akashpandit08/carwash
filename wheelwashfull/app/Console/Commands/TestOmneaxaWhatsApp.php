<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestOmneaxaWhatsApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omneaxa:test-event {phone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Omneaxa WhatsApp integration by sending a test event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $service = app(\App\Services\OmneaxaWhatsAppService::class);
            
            if (!config('omneaxa.whatsapp_enabled')) {
                $this->warn('Omneaxa WhatsApp is disabled by env.');
                return;
            }

            if (!$service->isEnabled()) {
                $this->error('Omneaxa WhatsApp config is missing. Check your .env file.');
                return;
            }

            $phone = $this->argument('phone');
            $this->info("Sending test event to {$phone}...");

            $success = $service->sendEvent(
                $phone,
                'test_event',
                ['message' => 'This is a test message from artisan.'],
                ['event_type' => 'test', 'module' => 'cli']
            );

            if ($success) {
                $this->info('Test event sent successfully!');
            } else {
                $this->error('Test event failed. Check omneaxa_whatsapp_attempts table or logs.');
            }
        } catch (\Throwable $e) {
            $this->error("Command crashed (unexpected): " . $e->getMessage());
        }
    }
}
