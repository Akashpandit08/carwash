import apiClient from './client';

export const getPartnerDashboard = () => apiClient.get('/operations/partner/dashboard');
export const getPartnerJobs = () => apiClient.get('/operations/partner/jobs');
export const getPartnerJobDetail = (bookingId: string | number) => apiClient.get(`/operations/partner/jobs/${bookingId}`);

export const getPartnerWorkers = () => apiClient.get('/operations/partner/workers'); // Assuming backend adds it if missing
export const assignWorkerToJob = (bookingId: string | number, workerId: string | number) => 
  apiClient.post(`/operations/partner/jobs/${bookingId}/assign-worker`, { worker_id: workerId });

export const updateJobStatus = (bookingId: string | number, status: string) => 
  apiClient.post(`/operations/partner/jobs/${bookingId}/status`, { status });

export const getPartnerEarnings = () => apiClient.get('/partner/earnings');
