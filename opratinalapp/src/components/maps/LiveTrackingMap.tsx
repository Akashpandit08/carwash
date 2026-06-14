import React, { useEffect, useMemo, useRef, useState } from 'react';
import { ActivityIndicator, StyleSheet, Text, View, Platform, TouchableOpacity } from 'react-native';
import MapView, { Marker, PROVIDER_GOOGLE, AnimatedRegion } from 'react-native-maps';
import { LatLng, minutesAgo, calculateBearing, openMapPoint } from '../../utils/maps';

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
  currentLocation,
  destination,
  trackedUserLocation,
  garageLocation,
  customerLocation,
  height = 240,
  loading = false,
}: LiveTrackingMapProps) {
  const mapRef = useRef<MapView | null>(null);
  const markers = useMemo(
    () => [currentLocation, destination, trackedUserLocation, garageLocation, customerLocation].filter(isValidLatLng) as LatLng[],
    [currentLocation, destination, trackedUserLocation, garageLocation, customerLocation]
  );

  // For animated marker
  const [animatedCoord] = useState(() => new AnimatedRegion({
    latitude: trackedUserLocation?.latitude || 0,
    longitude: trackedUserLocation?.longitude || 0,
    latitudeDelta: 0,
    longitudeDelta: 0,
  }));
  const previousCoord = useRef<LatLng | null>(trackedUserLocation ? { latitude: trackedUserLocation.latitude, longitude: trackedUserLocation.longitude } : null);
  const [bearing, setBearing] = useState(0);

  useEffect(() => {
    if (trackedUserLocation) {
      const newCoord = { latitude: trackedUserLocation.latitude, longitude: trackedUserLocation.longitude };

      if (previousCoord.current) {
        if (previousCoord.current.latitude !== newCoord.latitude || previousCoord.current.longitude !== newCoord.longitude) {
          const newBearing = calculateBearing(previousCoord.current, newCoord);
          setBearing(newBearing);

          if (Platform.OS === 'android') {
            animatedCoord.timing({
              latitude: newCoord.latitude,
              longitude: newCoord.longitude,
              duration: 2000,
              useNativeDriver: false,
              toValue: 0,
              latitudeDelta: 0,
              longitudeDelta: 0
            }).start();
          } else {
            animatedCoord.timing({
              latitude: newCoord.latitude,
              longitude: newCoord.longitude,
              latitudeDelta: 0,
              longitudeDelta: 0,
              duration: 2000,
              useNativeDriver: false,
              toValue: 0
            }).start();
          }
          previousCoord.current = newCoord;
        }
      } else {
        animatedCoord.setValue({
          latitude: newCoord.latitude,
          longitude: newCoord.longitude,
          latitudeDelta: 0,
          longitudeDelta: 0,
        });
        previousCoord.current = newCoord;
      }
    }
  }, [trackedUserLocation]);

  useEffect(() => {
    if (markers.length > 1) {
      setTimeout(() => mapRef.current?.fitToCoordinates(markers, {
        edgePadding: { top: 42, right: 42, bottom: 42, left: 42 },
        animated: true,
      }), 250);
    }
  }, [markers]);

  if (loading) {
    return <View style={[styles.fallback, { height }]}><ActivityIndicator color="#2563EB" /><Text style={styles.fallbackText}>Loading map...</Text></View>;
  }

  if (!markers.length) {
    return <View style={[styles.fallback, { height }]}><Text style={styles.fallbackText}>Location not available for this booking</Text></View>;
  }

  const initial = markers[0];

  return (
    <View style={[styles.wrap, { height }]}>
      <MapView
        ref={mapRef}
        provider={PROVIDER_GOOGLE}
        style={StyleSheet.absoluteFill}
        initialRegion={{ ...initial, latitudeDelta: 0.035, longitudeDelta: 0.035 }}
      >
        {isValidLatLng(currentLocation) && <Marker coordinate={currentLocation} title="You" pinColor="#2563EB" />}
        {isValidLatLng(destination) && <Marker coordinate={destination} title={destination.title || 'Destination'} pinColor="#DC2626" />}
        {isValidLatLng(customerLocation) && <Marker coordinate={customerLocation} title={customerLocation.title || 'Customer'} pinColor="#F97316" />}
        {isValidLatLng(garageLocation) && <Marker coordinate={garageLocation} title={garageLocation.title || 'Washing Center'} pinColor="#7C3AED" />}

        {isValidLatLng(trackedUserLocation) && (
          <Marker.Animated
            coordinate={animatedCoord as any}
            title={trackedUserLocation.title || roleTitle(trackedUserLocation.role)}
            description={`Last updated ${minutesAgo(trackedUserLocation.lastSeenAt)}`}
            rotation={bearing}
            anchor={{ x: 0.5, y: 0.5 }}
            flat={true}
          >
            <View style={styles.carMarkerContainer}>
              <View style={[styles.carMarker, { backgroundColor: trackedUserLocation.role === 'partner' ? '#059669' : '#2563EB' }]}>
                {/* Simulated headlights */}
                <View style={styles.headlight} />
                <View style={[styles.headlight, { right: 2, left: undefined }]} />
              </View>
            </View>
          </Marker.Animated>
        )}
      </MapView>
      <View style={styles.badgeRow}>
        {trackedUserLocation?.lastSeenAt ? (
          <View style={styles.badge}><Text style={styles.badgeText}>Last updated {minutesAgo(trackedUserLocation.lastSeenAt)}</Text></View>
        ) : <View style={{ flex: 1 }} />}
        
        <TouchableOpacity 
          style={styles.externalMapBtn} 
          onPress={() => {
             const target = customerLocation || destination || garageLocation || trackedUserLocation;
             if (target) {
                openMapPoint(target.latitude, target.longitude, target.title || 'Location');
             }
          }}
        >
          <Text style={styles.externalMapBtnText}>Open in Maps ↗</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

function isValidLatLng(value?: LatLng | null): value is LatLng {
  return typeof value?.latitude === 'number' && Number.isFinite(value.latitude)
    && typeof value?.longitude === 'number' && Number.isFinite(value.longitude);
}

function roleTitle(role?: TrackedRole) {
  switch (role) {
    case 'worker': return 'Worker';
    case 'pickup_driver': return 'Pickup Driver';
    case 'partner': return 'Partner';
    case 'customer': return 'Customer';
    default: return 'Tracked User';
  }
}

const styles = StyleSheet.create({
  wrap: { borderRadius: 8, overflow: 'hidden', backgroundColor: '#E5E7EB', marginBottom: 12 },
  fallback: { borderRadius: 8, backgroundColor: '#E5E7EB', alignItems: 'center', justifyContent: 'center', padding: 16, marginBottom: 12 },
  fallbackText: { color: '#475569', fontWeight: '700', marginTop: 8, textAlign: 'center' },
  badge: { backgroundColor: 'rgba(15,23,42,0.78)', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 8, alignSelf: 'flex-start' },
  badgeText: { color: '#FFFFFF', fontSize: 12, fontWeight: '700' },
  badgeRow: { position: 'absolute', bottom: 10, left: 10, right: 10, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-end', pointerEvents: 'box-none' },
  externalMapBtn: { backgroundColor: '#FFFFFF', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 8, elevation: 3, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.2, shadowRadius: 2 },
  externalMapBtnText: { color: '#2563EB', fontSize: 12, fontWeight: '700' },
  carMarkerContainer: {
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
  },
  carMarker: {
    width: 20,
    height: 36,
    borderRadius: 6,
    borderWidth: 2,
    borderColor: '#FFFFFF',
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    position: 'relative',
  },
  headlight: {
    position: 'absolute',
    top: 2,
    left: 2,
    width: 4,
    height: 4,
    borderRadius: 2,
    backgroundColor: '#FFEB3B',
  }
});
