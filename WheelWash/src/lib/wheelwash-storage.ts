import AsyncStorage from '@react-native-async-storage/async-storage';
import { defaultVehicle, STORAGE_KEYS, UserLocation, Vehicle } from './wheelwash-data';
export async function saveUser(user: unknown) {
  await AsyncStorage.setItem(STORAGE_KEYS.user, JSON.stringify(user));
}

export async function getLocation(): Promise<UserLocation | null> {
  const raw = await AsyncStorage.getItem(STORAGE_KEYS.location);
  return raw ? JSON.parse(raw) : null;
}

export async function saveLocation(location: UserLocation) {
  await AsyncStorage.setItem(STORAGE_KEYS.location, JSON.stringify(location));
}

export async function getSelectedVehicle(): Promise<Vehicle | null> {
  const raw = await AsyncStorage.getItem(STORAGE_KEYS.selectedVehicle);
  return raw ? JSON.parse(raw) : null;
}

export async function saveVehicle(vehicle: Vehicle) {
  const next = { ...defaultVehicle, ...vehicle, id: vehicle.id || String(Date.now()) };
  await AsyncStorage.setItem(STORAGE_KEYS.selectedVehicle, JSON.stringify(next));
  await AsyncStorage.setItem(STORAGE_KEYS.vehicleId, String(next.id));
  await AsyncStorage.setItem(STORAGE_KEYS.vehicles, JSON.stringify([next]));
  return next;
}
