import { Linking, Platform } from 'react-native';

export function openGoogleMaps(latitude?: number | string, longitude?: number | string, label?: string) {
  if (latitude === undefined || longitude === undefined || latitude === null || longitude === null) return;

  const lat = Number(latitude);
  const lng = Number(longitude);
  const encodedLabel = encodeURIComponent(label || 'Destination');

  const url = Platform.select({
    ios: `comgooglemaps://?daddr=${lat},${lng}&directionsmode=driving`,
    android: `google.navigation:q=${lat},${lng}`,
    default: `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`,
  });

  Linking.openURL(url || '').catch(() => {
    Linking.openURL(`https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}&destination_place_id=${encodedLabel}`);
  });
}

export function callPhone(phone?: string) {
  if (!phone) return;
  Linking.openURL(`tel:${phone}`);
}

export function openWhatsApp(phone?: string, message = '') {
  if (!phone) return;
  Linking.openURL(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`);
}

export function destinationForJob(job: any, phase?: string) {
  if (phase === 'pickup') {
    return {
      latitude: job?.pickup_latitude || job?.pickupAddress?.latitude || job?.latitude,
      longitude: job?.pickup_longitude || job?.pickupAddress?.longitude || job?.longitude,
      label: 'Customer pickup location',
    };
  }

  if (phase === 'delivery') {
    return {
      latitude: job?.drop_latitude || job?.delivery_latitude || job?.dropAddress?.latitude || job?.latitude,
      longitude: job?.drop_longitude || job?.delivery_longitude || job?.dropAddress?.longitude || job?.longitude,
      label: 'Customer delivery location',
    };
  }

  if (phase === 'partner') {
    return {
      latitude: job?.partner?.latitude || job?.partner?.partner_profile?.latitude || job?.partner_latitude,
      longitude: job?.partner?.longitude || job?.partner?.partner_profile?.longitude || job?.partner_longitude,
      label: 'Washing center',
    };
  }

  return {
    latitude: job?.latitude || job?.pickup_latitude,
    longitude: job?.longitude || job?.pickup_longitude,
    label: 'Customer location',
  };
}
