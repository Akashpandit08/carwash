import apiClient from './client';

export const getWorkerDashboard = () => apiClient.get('/operations/worker/dashboard');
export const getWorkerJobs = () => apiClient.get('/operations/worker/jobs');
export const getWorkerJobDetail = (bookingId: string | number) => apiClient.get(`/operations/worker/jobs/${bookingId}`);

export const updateJobStatus = (bookingId: string | number, status: string) => 
  apiClient.post(`/operations/worker/jobs/${bookingId}/status`, { status });

export const uploadBeforeImages = (bookingId: string | number, formData: FormData) => 
  apiClient.post(`/operations/worker/jobs/${bookingId}/media`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });

export const uploadAfterImages = (bookingId: string | number, formData: FormData) => 
  apiClient.post(`/operations/worker/jobs/${bookingId}/media`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });

export const getWorkerEarnings = () => apiClient.get('/operations/worker/earnings');
