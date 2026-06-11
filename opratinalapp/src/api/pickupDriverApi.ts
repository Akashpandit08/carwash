import apiClient from './client';

export const getDriverDashboard = () => apiClient.get('/operations/driver/dashboard');
export const getDriverJobs = () => apiClient.get('/operations/driver/jobs');
export const getDriverJobDetail = (bookingId: string | number) => apiClient.get(`/operations/driver/jobs/${bookingId}`);

export const updateJobStatus = (bookingId: string | number, status: string) => 
  apiClient.post(`/operations/driver/jobs/${bookingId}/status`, { status });

export const updateLocation = (latitude: number, longitude: number) => 
  apiClient.post('/operations/location/update', { latitude, longitude });

export const uploadPickupImages = (bookingId: string | number, formData: FormData) => 
  apiClient.post(`/operations/driver/jobs/${bookingId}/media`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });

export const uploadDeliveryImages = (bookingId: string | number, formData: FormData) => 
  apiClient.post(`/operations/driver/jobs/${bookingId}/media`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });

export const getDriverEarnings = () => apiClient.get('/operations/driver/earnings');
