import React from 'react';
import { View, Text, StyleSheet, ActivityIndicator, TouchableOpacity, Linking } from 'react-native';
import { LatLng, minutesAgo } from '../../utils/maps';

type TrackedRole = 'worker' | 'pickup_driver' | 'partner' | 'customer';

type MarkerLocation = LatLng & {
  title?: string;
  role?: TrackedRole;
  lastSeenAt?: string | null;
};

type LiveTrackingMapProps = {
  currentLocation?: LatLng;
  destination?: LatLng & { title?: string };
  trackedUserLocation?: MarkerLocation;
  garageLocation?: LatLng & { title?: string };
  customerLocation?: LatLng & { title?: string };
  height?: number;
  loading?: boolean;
};

export function LiveTrackingMap({
  trackedUserLocation,
  height = 240,
  loading = false,
}: LiveTrackingMapProps) {
  if (loading) {
    return (
      <View style={[styles.fallback, { height }]}>
        <ActivityIndicator color="#2563EB" />
        <Text style={styles.fallbackText}>Loading tracking...</Text>
      </View>
    );
  }

  if (!trackedUserLocation) {
    return (
      <View style={[styles.fallback, { height }]}>
        <Text style={styles.fallbackText}>Tracking will start when worker/driver starts the job.</Text>
      </View>
    );
  }

  const openGoogleMaps = () => {
    Linking.openURL(`https://www.google.com/maps/search/?api=1&query=${trackedUserLocation.latitude},${trackedUserLocation.longitude}`);
  };

  return (
    <View style={[styles.fallback, { height }]}>
      <Text style={styles.titleText}>📍 Live Tracking Fallback</Text>
      <Text style={styles.detailText}>Latitude: {trackedUserLocation.latitude}</Text>
      <Text style={styles.detailText}>Longitude: {trackedUserLocation.longitude}</Text>
      <Text style={styles.detailText}>Last updated: {minutesAgo(trackedUserLocation.lastSeenAt)}</Text>
      
      <TouchableOpacity style={styles.button} onPress={openGoogleMaps}>
        <Text style={styles.buttonText}>Open in Google Maps</Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  fallback: { borderRadius: 8, backgroundColor: '#E2E8F0', alignItems: 'center', justifyContent: 'center', padding: 16 },
  fallbackText: { color: '#475569', fontWeight: '700', marginTop: 8, textAlign: 'center' },
  titleText: { fontSize: 18, fontWeight: '800', color: '#1E293B', marginBottom: 12 },
  detailText: { fontSize: 14, color: '#475569', marginBottom: 6 },
  button: { marginTop: 16, backgroundColor: '#2563EB', paddingHorizontal: 20, paddingVertical: 10, borderRadius: 8 },
  buttonText: { color: '#FFFFFF', fontWeight: '700' }
});
