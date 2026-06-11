/**
 * API Configuration
 *
 * All API URLs are driven by environment variables.
 * Set EXPO_PUBLIC_API_URL and EXPO_PUBLIC_STORAGE_URL in your .env file.
 *
 * For mobile testing, use your laptop's WiFi IP (never 127.0.0.1).
 * Run Laravel with: php artisan serve --host=0.0.0.0 --port=8000
 */

export const API_BASE_URL =
  process.env.EXPO_PUBLIC_API_URL || 'http://192.168.1.10:8000/api';

export const STORAGE_URL =
  process.env.EXPO_PUBLIC_STORAGE_URL || 'http://192.168.1.10:8000/storage';
