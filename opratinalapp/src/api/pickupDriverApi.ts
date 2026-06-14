import AsyncStorage from '@react-native-async-storage/async-storage';
import apiClient, { API_BASE_URL } from './client';
import { parseUploadResponse } from '../utils/upload';

export const getDriverDashboard = () => apiClient.get('/pickup-driver/dashboard');
export const getDriverJobs = (tab = 'pickup') => apiClient.get('/pickup-driver/jobs', { params: { tab } });
export const getDriverJobDetail = (bookingId: string | number) => apiClient.get(`/pickup-driver/jobs/${bookingId}`);

export const postDriverAction = (api: string, payload: Record<string, any> = {}) => apiClient.post(api, payload);

export const updateLocation = (latitude: number, longitude: number) =>
  apiClient.post('/app/location/update', { latitude, longitude, role: 'pickup_driver', is_online: true });

export const uploadDriverPhoto = async (bookingId: string | number, type: string, side: string, uri: string) => {
  const data = new FormData();
  data.append('type', type);
  data.append('side', side);
  data.append('file', {
    uri,
    type: 'image/jpeg',
    name: `${type}_${side}.jpg`,
  } as any);

  const token = await AsyncStorage.getItem('userToken');
  const response = await fetch(`${API_BASE_URL}/pickup-driver/jobs/${bookingId}/media`, {
    method: 'POST',
    body: data,
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });
  return parseUploadResponse(response);
};

export const uploadPickupImages = async (bookingId: string | number, formData: FormData) => {
  const token = await AsyncStorage.getItem('userToken');
  const response = await fetch(`${API_BASE_URL}/pickup-driver/jobs/${bookingId}/media`, {
    method: 'POST',
    body: formData,
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });
  return parseUploadResponse(response);
};

export const uploadDeliveryImages = uploadPickupImages;

export const updateJobStatus = (bookingId: string | number, status: string) =>
  apiClient.post(`/pickup-driver/jobs/${bookingId}/status`, { status });

export const updateDriverOnlineStatus = (isOnline: boolean) =>
  apiClient.post('/app/online-status', { is_online: isOnline, role: 'pickup_driver' });

export const getDriverEarnings = () => apiClient.get('/pickup-driver/earnings');
export const getDriverProfile = () => apiClient.get('/pickup-driver/profile');
export const updateDriverProfile = (payload: Record<string, any>) => apiClient.put('/pickup-driver/profile', payload);
