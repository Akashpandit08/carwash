<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class WhatsAppService
{
    /**
     * Send WhatsApp message using Meta Cloud API.
     *
     * @param string $mobile
     * @param string $message
     * @return bool
     * @throws Exception
     */
    public function sendMessage(string $mobile, string $message): bool
    {
        try {
            $mobile = $this->normalizeMobileNumber($mobile);
            $token = config('services.whatsapp.token');
            $version = config('services.whatsapp.version');
            $phoneId = config('services.whatsapp.phone_number_id');

            if (!$token || !$phoneId) {
                // If not configured, log warning and simulate success to not block flows
                Log::warning("WhatsApp API not configured. Simulated message to {$mobile}.");
                return true;
            }

            $url = "https://graph.facebook.com/{$version}/{$phoneId}/messages";

            $response = Http::withToken($token)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $mobile,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $message,
                    ],
                ]);

            if (!$response->successful()) {
                throw new Exception("WhatsApp API Error: " . $response->body());
            }

            Log::info("WhatsApp message successfully sent to {$mobile}");
            return true;
        } catch (Exception $e) {
            Log::error("Failed to send WhatsApp message to {$mobile}. Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function normalizeMobileNumber(string $mobile): string
    {
        // Remove all non-numeric characters
        $mobile = preg_replace('/[^0-9]/', '', $mobile);

        $defaultCode = config('services.whatsapp.default_country_code', '91');

        // If it starts with 0, remove it and add default code
        if (str_starts_with($mobile, '0')) {
            $mobile = ltrim($mobile, '0');
            return $defaultCode . $mobile;
        }

        // If it is exactly 10 digits, assume it's missing the country code
        if (strlen($mobile) === 10) {
            return $defaultCode . $mobile;
        }

        return $mobile;
    }
}
