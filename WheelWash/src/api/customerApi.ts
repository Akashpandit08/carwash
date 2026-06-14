import { UserLocation } from '@/lib/wheelwash-data';
import { apiClient, listFrom, unwrap } from './client';

export type AppBanner = {
  id: string | number;
  title: string;
  subtitle?: string;
  image?: string;
  image_url?: string;
  position?: string;
  type?: 'screen' | 'service' | 'booking' | 'external' | 'none';
  redirect_type?: 'home' | 'services' | 'service_detail' | 'booking' | 'booking_detail' | 'offers' | 'profile' | 'external_url' | 'custom_screen';
  redirect_screen?: string;
  redirect_value?: string;
  sort_order?: number;
  background_color?: string;
  button_label?: string;
};

export async function getProfile() {
  const response = await apiClient.get('/customer/profile');
  return unwrap(response.data);
}

export async function getHome() {
  const response = await apiClient.get('/app/home');
  return unwrap(response.data);
}

export async function listBanners(position = 'home_top') {
  const response = await apiClient.get('/app/banners', { params: { position } });
  return listFrom<AppBanner>(response.data);
}

export async function saveDeviceToken(payload: {
  user_id?: string | number;
  role?: string;
  device_token?: string;
  expo_push_token?: string;
  fcm_token?: string;
  platform?: string;
  device_type?: string;
  device_name?: string;
}) {
  const response = await apiClient.post('/app/device-token', payload);
  return unwrap(response.data);
}

export async function listNotifications() {
  const response = await apiClient.get('/app/notifications');
  return listFrom(response.data);
}

export async function markNotificationRead(notificationId: string | number) {
  const response = await apiClient.post(`/app/notifications/${notificationId}/read`);
  return unwrap(response.data);
}

export async function markAllNotificationsRead() {
  const response = await apiClient.post('/app/notifications/read-all');
  return unwrap(response.data);
}

export async function listAddresses() {
  const response = await apiClient.get('/customer/addresses');
  return listFrom(response.data);
}

export async function createAddress(location: UserLocation) {
  const response = await apiClient.post('/customer/addresses', {
    type: 'home',
    full_address: location.fullAddress,
    city: location.city,
    state: location.region,
    pincode: location.pincode,
    latitude: location.latitude,
    longitude: location.longitude,
    is_default: true,
  });
  return unwrap(response.data);
}

export async function updateAddress(addressId: string | number, location: UserLocation) {
  const response = await apiClient.put(`/customer/addresses/${addressId}`, {
    type: 'home',
    full_address: location.fullAddress,
    city: location.city,
    state: location.region,
    pincode: location.pincode,
    latitude: location.latitude,
    longitude: location.longitude,
    is_default: true,
  });
  return unwrap(response.data);
}
