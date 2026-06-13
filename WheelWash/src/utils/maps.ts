import { Linking, Platform } from 'react-native';

export type LatLng = {
  latitude: number;
  longitude: number;
};

export function asLatLng(latitude?: number | string | null, longitude?: number | string | null): LatLng | undefined {
  if (latitude === undefined || latitude === null || longitude === undefined || longitude === null) return undefined;
  const lat = Number(latitude);
  const lng = Number(longitude);
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) return undefined;
  return { latitude: lat, longitude: lng };
}

export function openDirections(latitude: number, longitude: number, label = 'Destination') {
  const encodedLabel = encodeURIComponent(label);
  const nativeUrl = Platform.select({
    ios: `comgooglemaps://?daddr=${latitude},${longitude}&directionsmode=driving`,
    android: `google.navigation:q=${latitude},${longitude}`,
    default: `https://www.google.com/maps/dir/?api=1&destination=${latitude},${longitude}`,
  });
  const webUrl = `https://www.google.com/maps/dir/?api=1&destination=${latitude},${longitude}&destination_place_id=${encodedLabel}`;

  Linking.openURL(nativeUrl || webUrl).catch(() => Linking.openURL(webUrl));
}

export function openMapPoint(latitude: number, longitude: number, label = 'Destination') {
  const encodedLabel = encodeURIComponent(label);
  const url = Platform.select({
    ios: `maps:0,0?q=${encodedLabel}@${latitude},${longitude}`,
    android: `geo:${latitude},${longitude}?q=${latitude},${longitude}(${encodedLabel})`,
    default: `https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}`,
  });

  Linking.openURL(url || `https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}`);
}

export function minutesAgo(timestamp?: string | null) {
  if (!timestamp) return 'Not updated yet';
  const diff = Date.now() - new Date(timestamp).getTime();
  if (!Number.isFinite(diff) || diff < 0) return 'Just now';
  const minutes = Math.floor(diff / 60000);
  if (minutes < 1) return 'Just now';
  if (minutes === 1) return '1 min ago';
  return `${minutes} min ago`;
}

export function calculateBearing(start: LatLng, end: LatLng): number {
  const startLat = start.latitude * Math.PI / 180;
  const startLng = start.longitude * Math.PI / 180;
  const endLat = end.latitude * Math.PI / 180;
  const endLng = end.longitude * Math.PI / 180;

  const dLng = endLng - startLng;

  const y = Math.sin(dLng) * Math.cos(endLat);
  const x = Math.cos(startLat) * Math.sin(endLat) -
            Math.sin(startLat) * Math.cos(endLat) * Math.cos(dLng);

  let brng = Math.atan2(y, x);
  brng = brng * 180 / Math.PI;
  brng = (brng + 360) % 360;
  
  return brng;
}
