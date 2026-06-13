import * as Location from 'expo-location';
import { AppState } from 'react-native';
import apiClient from '../api/client';

let activeTracker: { key: string; stop: () => void } | null = null;

export async function ensureLocationPermission() {
  const { status } = await Location.requestForegroundPermissionsAsync();
  return status === 'granted';
}

export async function getCurrentCoords() {
  const granted = await ensureLocationPermission();
  if (!granted) {
    throw new Error('Location is required to receive and complete jobs.');
  }

  const location = await Location.getCurrentPositionAsync({});
  return {
    latitude: location.coords.latitude,
    longitude: location.coords.longitude,
  };
}

export async function sendLiveLocation(role: 'worker' | 'pickup_driver', isOnline = true, bookingId?: number | string) {
  const coords = await getCurrentCoords();
  await apiClient.post('/app/location/update', {
    ...coords,
    role,
    is_online: isOnline,
    booking_id: bookingId,
  });
  return coords;
}

export function startLiveTracking(role: 'worker' | 'pickup_driver', isOnline: () => boolean, bookingId?: number | string) {
  const key = `${role}:${bookingId || 'global'}`;
  if (activeTracker?.key === key) return activeTracker.stop;
  activeTracker?.stop();

  let stopped = false;

  const tick = async () => {
    if (!stopped && isOnline()) {
      try {
        await sendLiveLocation(role, true, bookingId);
      } catch {
        // The next tick will retry.
      }
    }
  };

  tick();
  const timer = setInterval(tick, 15000);
  const appStateSubscription = AppState.addEventListener('change', (state) => {
    if (state === 'active') tick();
  });

  const stop = () => {
    stopped = true;
    clearInterval(timer);
    appStateSubscription.remove();
    if (activeTracker?.key === key) activeTracker = null;
  };

  activeTracker = { key, stop };
  return stop;
}

export function stopLiveTracking() {
  activeTracker?.stop();
  activeTracker = null;
}
