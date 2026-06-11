import { apiClient, listFrom, unwrap } from './client';

export type VehicleDto = {
  id: string | number;
  vehicle_type?: string;
  type?: string;
  brand: string;
  model: string;
  registration_number?: string;
  number?: string;
  fuel_type?: string;
  color?: string;
};

export type VehiclePayload = {
  brand: string;
  model: string;
  number: string;
  fuel_type?: string;
  vehicle_type: string;
  color?: string;
};

function toBackend(payload: VehiclePayload) {
  return {
    vehicle_type: payload.vehicle_type,
    brand: payload.brand,
    model: payload.model,
    registration_number: payload.number.replace(/\s+/g, '').toUpperCase(),
    color: payload.color || payload.fuel_type || 'White',
  };
}

export async function listVehicles() {
  const response = await apiClient.get('/customer/vehicles');
  return listFrom<VehicleDto>(response.data);
}

export async function addVehicle(payload: VehiclePayload) {
  const response = await apiClient.post('/customer/vehicles', toBackend(payload));
  return unwrap<VehicleDto>(response.data);
}

export async function updateVehicle(id: string | number, payload: VehiclePayload) {
  const response = await apiClient.put(`/customer/vehicles/${id}`, toBackend(payload));
  return unwrap<VehicleDto>(response.data);
}

export async function deleteVehicle(id: string | number) {
  await apiClient.delete(`/customer/vehicles/${id}`);
}
