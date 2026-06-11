<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OmneaxaController extends Controller
{
    public function status()
    {
        return response()->json([
            'enabled' => config('omneaxa.whatsapp_enabled'),
            'configured' => app(\App\Services\OmneaxaWhatsAppService::class)->isEnabled(),
            'api_url' => config('omneaxa.api_url'),
            'tenant_slug' => config('omneaxa.tenant_slug'),
            'has_api_key' => !empty(config('omneaxa.api_key')),
            'timeout_seconds' => config('omneaxa.timeout_seconds'),
        ]);
    }
}
