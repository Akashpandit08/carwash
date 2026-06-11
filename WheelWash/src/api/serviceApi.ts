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
  services?: ServiceDto[];
};

function flattenServices(data: unknown): ServiceDto[] {
  const list = listFrom<ServiceDto>(data);
  return list.flatMap((item) => (Array.isArray(item.services) ? item.services : [item]));
}

export async function listServices() {
  const response = await apiClient.get('/customer/services');
  return flattenServices(response.data);
}

export async function getService(id: string | number) {
  const response = await apiClient.get(`/customer/services/${id}`);
  return unwrap<ServiceDto>(response.data);
}
