import * as WebBrowser from 'expo-web-browser';
import { router } from 'expo-router';

export type RedirectPayload = {
  redirect_type?: string;
  redirect_value?: string | number | null;
  screen?: string;
  booking_id?: string | number | null;
  type?: string;
};

export async function handleNotificationRedirect(data: RedirectPayload) {
  // Handle new operational notification screen-based routing
  const screen = data?.screen;
  const bookingId = data?.booking_id ? String(data.booking_id) : '';

  if (screen) {
    switch (screen) {
      case 'booking_tracking':
        if (bookingId) router.push({ pathname: '/track', params: { id: bookingId } } as never);
        return;
      case 'booking_detail':
        if (bookingId) router.push({ pathname: '/booking-detail', params: { id: bookingId } } as never);
        return;
      case 'payment':
        if (bookingId) router.push({ pathname: '/payment', params: { booking_id: bookingId } } as never);
        return;
    }
  }

  // Handle existing redirect_type-based routing (campaign notifications, banners)
  const type = data?.redirect_type || 'home';
  const value = data?.redirect_value ? String(data.redirect_value) : '';

  if (type === 'home') router.push('/(tabs)' as never);
  if (type === 'services') router.push('/services' as never);
  if (type === 'service_detail') router.push({ pathname: '/service-detail', params: { id: value } } as never);
  if (type === 'booking') router.push('/(tabs)/bookings' as never);
  if (type === 'booking_detail') router.push({ pathname: '/booking-detail', params: { id: value } } as never);
  if (type === 'offers') router.push('/(tabs)/offers' as never);
  if (type === 'profile') router.push('/(tabs)/profile' as never);
  if (type === 'external_url' && value) await WebBrowser.openBrowserAsync(value);
  if (type === 'custom_screen' && value) router.push(value as never);
}

