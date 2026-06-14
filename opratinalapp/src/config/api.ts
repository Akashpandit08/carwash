/**
 * API Configuration
 *
 * All API URLs are driven by environment variables with production defaults.
 */

export const API_BASE_URL =
  process.env.EXPO_PUBLIC_API_URL || 'http://127.0.0.1:8000/api';

export const BASE_URL = API_BASE_URL;

export const STORAGE_URL =
  process.env.EXPO_PUBLIC_STORAGE_URL || 'http://127.0.0.1:8000/storage';
