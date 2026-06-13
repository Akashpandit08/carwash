import AsyncStorage from '@react-native-async-storage/async-storage';
import { STORAGE_KEYS } from '@/lib/wheelwash-data';
import { apiClient, listFrom, unwrap } from './client';

export type SubscriptionPlanDto = {
  id: string | number;
  name: string;
  price: string | number;
  duration_days: number;
  total_washes: number;
  exterior_washes: number;
  interior_washes: number;
  foam_washes: number;
  max_washes_per_week?: number | null;
  pickup_drop_included?: boolean;
  doorstep_included?: boolean;
  tyre_polish_included?: boolean;
  dashboard_wipe_included?: boolean;
  vacuum_included?: boolean;
  priority_booking?: boolean;
  service_city_id?: number | null;
  service_zone_id?: number | null;
};

export type CustomerSubscriptionDto = {
  id: string | number;
  subscription_plan?: SubscriptionPlanDto;
  remaining_washes: number;
  total_washes: number;
  exterior_remaining: number;
  interior_remaining: number;
  foam_remaining: number;
  payment_status: string;
  status: string;
};

async function cityParams() {
  const [[, cityId], [, zoneId]] = await AsyncStorage.multiGet([STORAGE_KEYS.serviceCityId, STORAGE_KEYS.serviceZoneId]);
  return {
    ...(cityId ? { service_city_id: cityId } : {}),
    ...(zoneId ? { service_zone_id: zoneId } : {}),
  };
}

export async function listSubscriptionPlans() {
  const response = await apiClient.get('/app/subscription-plans', { params: await cityParams() });
  return listFrom<SubscriptionPlanDto>(response.data);
}

export async function purchaseSubscription(planId: string | number, payload: Record<string, unknown> = {}) {
  const response = await apiClient.post('/app/subscriptions/purchase', {
    subscription_plan_id: planId,
    ...(await cityParams()),
    ...payload,
  });
  return unwrap<CustomerSubscriptionDto>(response.data);
}

export async function listMySubscriptions() {
  const response = await apiClient.get('/app/my-subscriptions');
  return listFrom<CustomerSubscriptionDto>(response.data);
}

export async function bookSubscriptionWash(subscriptionId: string | number, payload: Record<string, unknown>) {
  const response = await apiClient.post(`/app/subscriptions/${subscriptionId}/book-wash`, payload);
  return unwrap(response.data);
}
