import apiClient from './client';

export const getDashboard = () => apiClient.get('/admin'); // Or /admin/dashboard depending on exact route config
export const getBookings = () => apiClient.get('/admin/bookings');
export const getBookingDetail = (id: string | number) => apiClient.get(`/admin/bookings/${id}`);

export const assignPickupDriver = (bookingId: string | number, driverId: string | number) => 
  apiClient.post(`/admin/bookings/${bookingId}/assign-pickup-driver`, { driver_id: driverId });

export const assignPartner = (bookingId: string | number, partnerId: string | number) => 
  apiClient.post(`/admin/bookings/${bookingId}/assign-partner`, { partner_id: partnerId });

export const assignWorker = (bookingId: string | number, workerId: string | number) => 
  apiClient.post(`/admin/bookings/${bookingId}/assign-worker`, { worker_id: workerId });

export const updateBookingStatus = (bookingId: string | number, status: string) => 
  apiClient.post(`/admin/bookings/${bookingId}/status`, { status });

export const getPartners = () => apiClient.get('/admin/partners');
export const getWorkers = () => apiClient.get('/admin/workers');
export const getPickupDrivers = () => apiClient.get('/admin/pickup-drivers');

export const getServices = () => apiClient.get('/admin/services');
export const getSlots = () => apiClient.get('/admin/slots');
export const getCoupons = () => apiClient.get('/admin/coupons');
export const getReports = () => apiClient.get('/admin/reports'); // Assuming backend will add this
