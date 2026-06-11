<?php

return [
    'whatsapp_enabled' => env('OMNEAXA_WHATSAPP_ENABLED', false),
    'api_url' => env('OMNEAXA_API_URL'),
    'api_key' => env('OMNEAXA_API_KEY'),
    'tenant_slug' => env('OMNEAXA_TENANT_SLUG'),
    'default_language' => env('OMNEAXA_DEFAULT_LANGUAGE', 'en'),
    'timeout_seconds' => env('OMNEAXA_TIMEOUT_SECONDS', 5),
];
