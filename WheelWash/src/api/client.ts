import AsyncStorage from '@react-native-async-storage/async-storage';
import axios, { AxiosError } from 'axios';
import { router } from 'expo-router';
import { API_BASE_URL } from '@/config/api';
import { STORAGE_KEYS } from '@/lib/wheelwash-data';

export type ApiEnvelope<T> = {
  success?: boolean;
  message?: string;
  data?: T;
  errors?: Record<string, string[]>;
};

export class ApiError extends Error {
  status?: number;
  errors?: Record<string, string[]>;

  constructor(message: string, status?: number, errors?: Record<string, string[]>) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errors = errors;
  }
}

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 20000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

apiClient.interceptors.request.use(async (config) => {
  const token = await AsyncStorage.getItem(STORAGE_KEYS.customerToken);
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError<ApiEnvelope<unknown>>) => {
    if (error.response?.status === 401) {
      await AsyncStorage.multiRemove([
        STORAGE_KEYS.customerToken,
        STORAGE_KEYS.user,
        STORAGE_KEYS.selectedVehicle,
      ]);
      router.replace('/login');
    }

    const data = error.response?.data;
    const validationMessage = data?.errors
      ? Object.values(data.errors).flat().filter(Boolean).join('\n')
      : '';
    const message =
      validationMessage ||
      data?.message ||
      (error.request ? `Network error. Phone cannot reach API at ${API_BASE_URL}. Check Laravel host/firewall and same Wi-Fi.` : 'Something went wrong.');

    throw new ApiError(message, error.response?.status, data?.errors);
  },
);

export function unwrap<T>(payload: ApiEnvelope<T> | T): T {
  if (payload && typeof payload === 'object' && 'data' in payload) {
    return (payload as ApiEnvelope<T>).data as T;
  }
  return payload as T;
}

export function listFrom<T>(payload: unknown): T[] {
  const data = unwrap<unknown>(payload as ApiEnvelope<unknown>);
  if (Array.isArray(data)) return data as T[];
  if (data && typeof data === 'object' && Array.isArray((data as { data?: unknown[] }).data)) {
    return (data as { data: T[] }).data;
  }
  return [];
}
