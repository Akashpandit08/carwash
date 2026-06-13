import apiClient, { API_BASE_URL } from './client';
import AsyncStorage from '@react-native-async-storage/async-storage';
export const getWorkerDashboard = () => apiClient.get('/worker/dashboard');
export const getWorkerJobs = (tab = 'today') => apiClient.get('/worker/jobs', { params: { tab } });
export const getWorkerJobDetail = (bookingId: string | number) => apiClient.get(`/worker/jobs/${bookingId}`);

export const postWorkerAction = (api: string, payload: Record<string, any> = {}) => apiClient.post(api, payload);

export const uploadWorkerPhoto = async (bookingId: string | number, type: string, side: string, uri: string) => {
  const data = new FormData();
  data.append('type', type);
  data.append('side', side);
  data.append('file', {
    uri,
    type: 'image/jpeg',
    name: `${type}_${side}.jpg`,
  } as any);

  const token = await AsyncStorage.getItem('userToken');
  const response = await fetch(`${API_BASE_URL}/worker/jobs/${bookingId}/media`, {
    method: 'POST',
    body: data,
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
      'Content-Type': 'multipart/form-data',
    },
  });

  const responseJson = await response.json();
  if (!response.ok) {
    throw new Error(responseJson.message || 'Upload failed');
  }
  return responseJson;
};

export const uploadBeforeImages = async (bookingId: string | number, formData: FormData) => {
  const token = await AsyncStorage.getItem('userToken');
  const response = await fetch(`${API_BASE_URL}/worker/jobs/${bookingId}/media`, {
    method: 'POST',
    body: formData,
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
      'Content-Type': 'multipart/form-data',
    },
  });
  const responseJson = await response.json();
  if (!response.ok) throw new Error(responseJson.message || 'Upload failed');
  return responseJson;
};

export const uploadAfterImages = uploadBeforeImages;

export const updateJobStatus = (bookingId: string | number, status: string) =>
  apiClient.post(`/worker/jobs/${bookingId}/status`, { status });

export const updateOnlineStatus = (isOnline: boolean, role = 'worker') =>
  apiClient.post('/app/online-status', { is_online: isOnline, role });

export const getWorkerEarnings = () => apiClient.get('/worker/earnings');
export const getWorkerProfile = () => apiClient.get('/worker/profile');
export const updateWorkerProfile = (payload: Record<string, any>) => apiClient.put('/worker/profile', payload);
