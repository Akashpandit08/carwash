import apiClient from './client';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { UserRole } from '../constants/roles';

const withAdminCity = async (data: any = {}) => {
  const userData = await AsyncStorage.getItem('userData');
  const user = userData ? JSON.parse(userData) : null;

  if (user?.role === UserRole.CITY_ADMIN) {
    return {
      ...data,
      service_city_id: user.service_city_id,
      service_zone_id: user.service_zone_id ?? data.service_zone_id,
    };
  }

  return data;
};

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
export const getPartnerDetail = (id: string | number) => apiClient.get(`/admin/partners/${id}`);
export const createPartner = async (data: any) => apiClient.post('/admin/partners', await withAdminCity(data));
export const updatePartner = async (id: string | number, data: any) => apiClient.put(`/admin/partners/${id}`, await withAdminCity(data));
export const togglePartnerStatus = (id: string | number) => apiClient.patch(`/admin/partners/${id}/toggle-status`);

export const getWorkers = () => apiClient.get('/admin/workers');
export const getWorkerDetail = (id: string | number) => apiClient.get(`/admin/workers/${id}`);
export const createWorker = async (data: any) => apiClient.post('/admin/workers', await withAdminCity(data));
export const updateWorker = async (id: string | number, data: any) => apiClient.put(`/admin/workers/${id}`, await withAdminCity(data));
export const toggleWorkerStatus = (id: string | number) => apiClient.patch(`/admin/workers/${id}/toggle-status`);

export const getPickupDrivers = () => apiClient.get('/admin/pickup-drivers');
export const getPickupDriverDetail = (id: string | number) => apiClient.get(`/admin/pickup-drivers/${id}`);
export const createPickupDriver = async (data: any) => apiClient.post('/admin/pickup-drivers', await withAdminCity(data));
export const updatePickupDriver = async (id: string | number, data: any) => apiClient.put(`/admin/pickup-drivers/${id}`, await withAdminCity(data));
export const togglePickupDriverStatus = (id: string | number) => apiClient.patch(`/admin/pickup-drivers/${id}/toggle-status`);

export const getServices = (params: any = {}) => apiClient.get('/admin/services', { params });
export const createService = async (data: any) => apiClient.post('/admin/services', await withAdminCity(data));
export const updateService = async (id: string | number, data: any) => apiClient.put(`/admin/services/${id}`, await withAdminCity(data));
export const deleteService = (id: string | number) => apiClient.delete(`/admin/services/${id}`);
export const getSlots = () => apiClient.get('/admin/slots');
export const createSlot = (data: any) => apiClient.post('/admin/slots', data);
export const updateSlot = (id: string | number, data: any) => apiClient.put(`/admin/slots/${id}`, data);
export const deleteSlot = (id: string | number) => apiClient.delete(`/admin/slots/${id}`);

export const getCoupons = () => apiClient.get('/admin/coupons');
export const createCoupon = (data: any) => apiClient.post('/admin/coupons', data);
export const updateCoupon = (id: string | number, data: any) => apiClient.put(`/admin/coupons/${id}`, data);
export const deleteCoupon = (id: string | number) => apiClient.delete(`/admin/coupons/${id}`);

export const getReports = () => apiClient.get('/admin/reports'); // Assuming backend will add this
export const getCities = () => apiClient.get('/admin/cities');
export const createCity = (data: any) => apiClient.post('/admin/cities', data);
export const updateCity = (id: string | number, data: any) => apiClient.put(`/admin/cities/${id}`, data);
export const deleteCity = (id: string | number) => apiClient.delete(`/admin/cities/${id}`);

export const getZones = (serviceCityId?: string | number) => apiClient.get('/admin/zones', { params: { service_city_id: serviceCityId } });
export const getCityAdmins = () => apiClient.get('/admin/city-admins');
export const createCityAdmin = (data: any) => apiClient.post('/admin/city-admins', data);
export const updateCityAdmin = (id: string | number, data: any) => apiClient.put(`/admin/city-admins/${id}`, data);
export const deleteCityAdmin = (id: string | number) => apiClient.delete(`/admin/city-admins/${id}`);

export const getPayouts = () => apiClient.get('/admin/payouts');
export const approvePayout = (id: string | number) => apiClient.post(`/admin/payouts/${id}/approve`);
export const rejectPayout = (id: string | number) => apiClient.post(`/admin/payouts/${id}/reject`);
export const markPayoutPaid = (id: string | number) => apiClient.post(`/admin/payouts/${id}/mark-paid`);

export const getSubscriptionPlans = (params: any = {}) => apiClient.get('/admin/subscription-plans', { params });
export const createSubscriptionPlan = async (data: any) => apiClient.post('/admin/subscription-plans', await withAdminCity(data));
export const updateSubscriptionPlan = async (id: string | number, data: any) => apiClient.put(`/admin/subscription-plans/${id}`, await withAdminCity(data));
export const deleteSubscriptionPlan = (id: string | number) => apiClient.delete(`/admin/subscription-plans/${id}`);

export const getCustomerSubscriptions = (params: any = {}) => apiClient.get('/admin/subscriptions', { params });
export const getCustomerSubscriptionDetail = (id: string | number) => apiClient.get(`/admin/subscriptions/${id}`);
export const activateCustomerSubscription = (id: string | number) => apiClient.post(`/admin/subscriptions/${id}/activate`);
export const cancelCustomerSubscription = (id: string | number) => apiClient.post(`/admin/subscriptions/${id}/cancel`);
export const markCustomerSubscriptionPaid = (id: string | number) => apiClient.post(`/admin/subscriptions/${id}/mark-paid`);
