import AsyncStorage from '@react-native-async-storage/async-storage';
import { STORAGE_KEYS, UserLocation } from '@/lib/wheelwash-data';
import { createAddress } from './customerApi';
import { apiClient, listFrom, unwrap } from './client';

export type ServiceMode = 'doorstep' | 'partner_center' | 'pickup_drop';
export type WashType = 'door_to_door' | 'pickup_wash';

export type AvailableSlot = {
  id?: string | number;
  time: string;
  available: boolean;
  available_count?: number;
  max_bookings?: number;
  worker_available?: boolean;
  pickup_driver_available?: boolean;
  partner_available?: boolean;
  delivery_driver_available?: boolean;
  nearest_distance_km?: number | null;
  nearest_pickup_driver_distance_km?: number | null;
  nearest_partner_distance_km?: number | null;
};

export type BookingDto = {
  id: string | number;
  status?: string;
  wash_type?: WashType;
  service_mode?: ServiceMode;
  booking_date?: string;
  booking_time?: string;
  address?: string;
  total_amount?: number | string;
  service?: { id?: string | number; name?: string; title?: string; price?: number | string; duration?: number | string };
  vehicle?: { brand?: string; model?: string; registration_number?: string; color?: string };
  partner?: { name?: string; mobile_number?: string };
  worker?: { name?: string; mobile_number?: string };
  pickup_driver?: { name?: string; mobile_number?: string };
  latest_payment?: { id?: string | number; status?: string };
  status_logs?: Array<{ status: string; note?: string; created_at?: string }>;
  media?: Array<{ type?: string; url?: string; file_url?: string }>;
};

export type CreateBookingPayload = {
  vehicle_id: string | number;
  service_id: string | number;
  service_mode?: ServiceMode;
  wash_type?: WashType;
  booking_date: string;
  booking_time: string;
  address: string;
  latitude?: number;
  longitude?: number;
  address_id?: string | number;
  pickup_address_id?: string | number;
  drop_address_id?: string | number;
  payment_method: 'cod' | 'online' | 'subscription';
  customer_subscription_id?: string | number;
};

export async function listBookings() {
  const response = await apiClient.get('/customer/bookings');
  return listFrom<BookingDto>(response.data);
}

export async function getBooking(id: string | number) {
  const response = await apiClient.get(`/customer/bookings/${id}`);
  return unwrap<BookingDto>(response.data);
}

export async function trackBooking(id: string | number) {
  const response = await apiClient.get(`/customer/bookings/${id}/tracking`);
  return unwrap(response.data);
}

export async function createBooking(payload: CreateBookingPayload) {
  const response = await apiClient.post('/customer/bookings', payload);
  return unwrap<BookingDto>(response.data);
}

export async function getAvailableSlots(input: {
  serviceId?: string | number;
  washType: WashType;
  latitude: number;
  longitude: number;
  date: string;
}) {
  const response = await apiClient.get('/customer/available-slots', {
    params: {
      service_id: input.serviceId,
      wash_type: input.washType,
      latitude: input.latitude,
      longitude: input.longitude,
      date: input.date,
    },
  });

  return ((response.data as { slots?: AvailableSlot[] }).slots || []).filter(Boolean);
}

export async function createBookingFromSelection(input: {
  vehicleId: string | number;
  serviceId: string | number;
  serviceMode?: ServiceMode;
  washType?: WashType;
  bookingDate: string;
  bookingTime: string;
  location: UserLocation;
  paymentMethod?: 'cod' | 'online' | 'subscription';
  customerSubscriptionId?: string | number;
}) {
  let addressId = input.location.id || await AsyncStorage.getItem(STORAGE_KEYS.addressId);

  if (!addressId) {
    const address = await createAddress(input.location) as { id?: string | number };
    addressId = address?.id ? String(address.id) : null;
    if (!addressId) {
      throw new Error('Please save your service address before booking.');
    }
    await AsyncStorage.setItem(STORAGE_KEYS.addressId, addressId);
  }

  return createBooking({
    vehicle_id: input.vehicleId,
    service_id: input.serviceId,
    service_mode: input.serviceMode,
    wash_type: input.washType,
    booking_date: input.bookingDate,
    booking_time: input.bookingTime,
    address: input.location.fullAddress,
    latitude: input.location.latitude,
    longitude: input.location.longitude,
    address_id: addressId,
    pickup_address_id: input.washType === 'pickup_wash' ? addressId : undefined,
    drop_address_id: input.washType === 'pickup_wash' ? addressId : undefined,
    payment_method: input.paymentMethod || 'cod',
    customer_subscription_id: input.customerSubscriptionId,
  });
}
