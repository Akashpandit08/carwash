import AsyncStorage from '@react-native-async-storage/async-storage';
import { STORAGE_KEYS } from '@/lib/wheelwash-data';
import { apiClient, listFrom, unwrap } from './client';

export type ServiceDto = {
  id: string | number;
  name?: string;
  title?: string;
  description?: string;
  short_description?: string;
  price?: number | string;
  duration?: number | string;
  duration_minutes?: number | string;
  category?: string;
  image?: string;
  image_url?: string;
  service_city_id?: number;
  service_zone_id?: number | null;
  is_global?: boolean;
  status?: string;
  services?: ServiceDto[];
};

function flattenServices(data: unknown): ServiceDto[] {
  const list = listFrom<ServiceDto>(data);
  return list.flatMap((item) => (Array.isArray(item.services) ? item.services : [item]));
}

async function cityParams() {
  const [[, cityId], [, zoneId]] = await AsyncStorage.multiGet([STORAGE_KEYS.serviceCityId, STORAGE_KEYS.serviceZoneId]);
  return {
    ...(cityId ? { service_city_id: cityId } : {}),
    ...(zoneId ? { service_zone_id: zoneId } : {}),
  };
}

export async function listServices() {
  const response = await apiClient.get('/app/services', { params: await cityParams() });
  return flattenServices(response.data);
}

export async function getService(id: string | number) {
  const response = await apiClient.get(`/app/services/${id}`, { params: await cityParams() });
  return unwrap<ServiceDto>(response.data);
}
