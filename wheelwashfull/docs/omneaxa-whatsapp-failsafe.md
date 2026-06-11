# Omneaxa WhatsApp Fail-safe Integration

This document outlines the fail-safe integration for sending WhatsApp messages using the Omneaxa API within the WheelWash backend.

## Overview

The Omneaxa WhatsApp integration is designed to **never break the core app flow** (bookings, payments, OTPs, etc.), even if the Omneaxa API is down, slow, or incorrectly configured.

## Configuration

All configuration is handled via environment variables in your `.env` file.

### 1. How to Enable WhatsApp
To enable the integration, set the following variable to `true`:
```env
OMNEAXA_WHATSAPP_ENABLED=true
```

You must also provide the required credentials:
```env
OMNEAXA_API_URL=https://api.omneaxa.in/api/v1
OMNEAXA_API_KEY=omx_ext_xxxxx
OMNEAXA_TENANT_SLUG=wheelwash
```

### 2. How to Disable WhatsApp
If you need to quickly turn off WhatsApp notifications without changing code, simply set:
```env
OMNEAXA_WHATSAPP_ENABLED=false
```

### 3. Production Recommended Setting
To ensure that slow API responses do not delay the app for users, a strict timeout is enforced. The recommended production setting is 5 seconds:
```env
OMNEAXA_TIMEOUT_SECONDS=5
```

## Fail-safe Behavior

### What happens when disabled?
If `OMNEAXA_WHATSAPP_ENABLED=false` (or if it is missing), the app skips sending the message completely.
- **Booking, payment, and status changes continue to work normally.**
- The attempt is logged as `skipped` in the `omneaxa_whatsapp_attempts` database table.

### What happens when Omneaxa is down or times out?
If the Omneaxa API goes down, returns a 500 error, or takes longer than the configured timeout:
- The `OmneaxaWhatsAppService` catches the error internally.
- The user's operation (like completing a payment or updating a booking status) **continues flawlessly**.
- The failure is logged in the `omneaxa_whatsapp_attempts` table with the exact HTTP status or exception message.
- You can review the `omneaxa_whatsapp_attempts` table in your database to see failed messages.

## Security
- The `OMNEAXA_API_KEY` is **never** sent to the Expo frontend apps.
- All requests happen strictly server-to-server.
- Ensure that the `.env` file containing the API key is not committed to version control.

## Testing & Monitoring
- **Admin API:** You can check the configuration status (without revealing the API key) by hitting `GET /api/admin/omneaxa-whatsapp/status` using an Admin token.
- **Artisan Command:** Test the integration directly from your server command line:
  ```bash
  php artisan omneaxa:test-event 9876543210
  ```
