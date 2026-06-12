/**
 * API Configuration
 *
 * All API URLs are driven by environment variables with production defaults.
 */

export const API_BASE_URL =
  process.env.EXPO_PUBLIC_API_URL || 'https://wheelwash.gutargu.app/api';

export const BASE_URL = API_BASE_URL;

export const STORAGE_URL =
  process.env.EXPO_PUBLIC_STORAGE_URL || 'https://wheelwash.gutargu.app/storage';
