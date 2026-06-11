<?php

namespace App\Services;

use App\Models\OmneaxaWhatsappAttempt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OmneaxaWhatsAppService
{
    /**
     * Check if the Omneaxa WhatsApp integration is fully configured and enabled.
     */
    public function isEnabled(): bool
    {
        if (!config('omneaxa.whatsapp_enabled')) {
            return false;
        }

        if (empty(config('omneaxa.api_url'))) {
            return false;
        }

        if (empty(config('omneaxa.api_key'))) {
            return false;
        }

        if (empty(config('omneaxa.tenant_slug'))) {
            return false;
        }

        return true;
    }

    /**
     * Normalizes phone number format.
     */
    private function normalizePhone(string $phone): string
    {
        // Remove spaces, dashes, parentheses
        $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);
        
        // Remove leading '+' if present
        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        // Add '91' if it's a 10-digit number
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        return $phone;
    }

    /**
     * Send an event to Omneaxa.
     */
    public function sendEvent(
        string $phone,
        string $templateName,
        array $parameters,
        array $meta = [],
        ?string $language = null
    ): bool {
        // Prepare attempt log
        $attempt = new OmneaxaWhatsappAttempt([
            'phone' => $phone,
            'template_name' => $templateName,
            'event_type' => $meta['event_type'] ?? null,
            'module' => $meta['module'] ?? null,
            'user_id' => $meta['user_id'] ?? null,
            'role' => $meta['role'] ?? null,
            'booking_id' => $meta['booking_id'] ?? null,
            'endpoint' => '/external/whatsapp/send-event',
            'status' => 'failed',
        ]);

        if (!$this->isEnabled()) {
            $attempt->status = 'skipped';
            $attempt->error = 'Omneaxa WhatsApp is disabled or missing config.';
            $attempt->save();
            Log::info('Omneaxa WhatsApp skipped: disabled or missing config.', ['template' => $templateName]);
            return false;
        }

        if (empty($phone)) {
            $attempt->status = 'skipped';
            $attempt->error = 'Missing phone number.';
            $attempt->save();
            Log::warning('Omneaxa WhatsApp skipped: missing phone number.', ['template' => $templateName]);
            return false;
        }

        $normalizedPhone = $this->normalizePhone($phone);
        $attempt->phone = $normalizedPhone;

        $payload = [
            'to' => $normalizedPhone,
            'template_name' => $templateName,
            'language' => $language ?? config('omneaxa.default_language'),
            'parameters' => $parameters,
            'meta' => $meta,
        ];
        
        $attempt->payload = $payload;

        try {
            $url = rtrim(config('omneaxa.api_url'), '/') . '/external/whatsapp/send-event';
            $attempt->endpoint = $url;

            $response = Http::timeout(config('omneaxa.timeout_seconds', 5))
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('omneaxa.api_key'),
                    'X-Tenant-Slug' => config('omneaxa.tenant_slug'),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

            $attempt->response = $response->json() ?? [];

            if ($response->successful()) {
                $attempt->status = 'sent';
                $attempt->save();
                return true;
            }

            // HTTP failed
            $attempt->status = 'failed';
            $attempt->error = 'HTTP ' . $response->status() . ': ' . $response->body();
            $attempt->save();

            Log::error('Omneaxa WhatsApp failed (HTTP error)', [
                'status' => $response->status(),
                'template' => $templateName,
                'phone' => $normalizedPhone,
            ]);

            return false;

        } catch (\Throwable $e) {
            $attempt->status = 'failed';
            $attempt->error = 'Exception: ' . $e->getMessage();
            $attempt->save();

            Log::error('Omneaxa WhatsApp failed (Exception) but flow continued', [
                'error' => $e->getMessage(),
                'template' => $templateName,
                'phone' => $normalizedPhone,
            ]);

            return false;
        }
    }
}
