import React, { useEffect, useMemo, useState } from 'react';
import { View, Text, StyleSheet, ActivityIndicator, Platform } from 'react-native';
import { MapContainer, TileLayer, Marker as LeafletMarker, Popup, useMap } from 'react-leaflet';
import L from 'leaflet';
import { LatLng, minutesAgo, calculateBearing } from '../../utils/maps';

// This is required to fix leaflet marker icon issues with webpack/metro
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: require('leaflet/dist/images/marker-icon-2x.png'),
  iconUrl: require('leaflet/dist/images/marker-icon.png'),
  shadowUrl: require('leaflet/dist/images/marker-shadow.png'),
});

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

// Custom car icon for the tracked user
const createCarIcon = (bearing: number, role?: TrackedRole) => {
  const isWorker = role === 'worker' || role === 'pickup_driver';
  const color = isWorker ? '#2563EB' : '#059669'; // Blue for worker, Green for partner
  
  // A simple top-down car SVG that can be rotated
  const svg = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32" transform="rotate(${bearing})">
      <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.85 7h10.29l1.08 3.11H5.77L6.85 7zM19 17H5v-5h14v5z" fill="${color}" />
      <circle cx="7.5" cy="14.5" r="1.5" fill="#fff" />
      <circle cx="16.5" cy="14.5" r="1.5" fill="#fff" />
    </svg>
  `;
  
  return L.divIcon({
    html: svg,
    className: 'custom-car-icon',
    iconSize: [32, 32],
    iconAnchor: [16, 16],
  });
};

function roleTitle(role?: TrackedRole) {
  switch (role) {
    case 'worker': return 'Worker';
    case 'pickup_driver': return 'Pickup Driver';
    case 'partner': return 'Partner';
    case 'customer': return 'Customer';
    default: return 'Tracked User';
  }
}

// Component to handle auto-fitting the bounds
const MapBoundsFitter = ({ markers }: { markers: LatLng[] }) => {
  const map = useMap();
  useEffect(() => {
    if (markers.length > 0) {
      const bounds = L.latLngBounds(markers.map(m => [m.latitude, m.longitude]));
      map.fitBounds(bounds, { padding: [42, 42], maxZoom: 16 });
    }
  }, [markers, map]);
  return null;
};

// Component to handle smooth marker animation in leaflet
const AnimatedTrackedMarker = ({ location }: { location: MarkerLocation }) => {
  const [currentCoord, setCurrentCoord] = useState<[number, number]>([location.latitude, location.longitude]);
  const [bearing, setBearing] = useState(0);

  useEffect(() => {
    const newCoord: [number, number] = [location.latitude, location.longitude];
    const newBearing = calculateBearing(
      { latitude: currentCoord[0], longitude: currentCoord[1] },
      { latitude: newCoord[0], longitude: newCoord[1] }
    );
    
    // Only update bearing if they actually moved
    if (newCoord[0] !== currentCoord[0] || newCoord[1] !== currentCoord[1]) {
      setBearing(newBearing);
      setCurrentCoord(newCoord);
    }
  }, [location.latitude, location.longitude]);

  return (
    <LeafletMarker 
      position={currentCoord} 
      icon={createCarIcon(bearing, location.role)}
    >
      <Popup>
        <strong>{location.title || roleTitle(location.role)}</strong><br/>
        Last updated: {minutesAgo(location.lastSeenAt)}
      </Popup>
    </LeafletMarker>
  );
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
  if (loading) {
    return (
      <View style={[styles.fallback, { height }]}>
        <ActivityIndicator color="#2563EB" />
        <Text style={styles.fallbackText}>Loading map...</Text>
      </View>
    );
  }

  const markers = [currentLocation, destination, trackedUserLocation, garageLocation, customerLocation].filter(Boolean) as LatLng[];

  if (!markers.length) {
    return <View style={[styles.fallback, { height }]}><Text style={styles.fallbackText}>Location not available for this booking</Text></View>;
  }

  const initial = markers[0];

  return (
    <View style={[styles.wrap, { height }]}>
      <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
      <style>{`
        .leaflet-container { width: 100%; height: 100%; }
        .custom-car-icon {
          transition: transform 1.5s linear; /* Smooth CSS transition for movement */
          background: transparent;
          border: none;
        }
      `}</style>
      <MapContainer 
        center={[initial.latitude, initial.longitude]} 
        zoom={13} 
        scrollWheelZoom={true}
        style={{ width: '100%', height: '100%', zIndex: 0 }}
      >
        <TileLayer
          attribution='&copy; OpenStreetMap'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        
        {currentLocation && (
          <LeafletMarker position={[currentLocation.latitude, currentLocation.longitude]}>
            <Popup>You</Popup>
          </LeafletMarker>
        )}
        
        {destination && (
          <LeafletMarker position={[destination.latitude, destination.longitude]}>
            <Popup>{destination.title || 'Destination'}</Popup>
          </LeafletMarker>
        )}
        
        {customerLocation && (
          <LeafletMarker position={[customerLocation.latitude, customerLocation.longitude]}>
            <Popup>{customerLocation.title || 'Customer'}</Popup>
          </LeafletMarker>
        )}
        
        {garageLocation && (
          <LeafletMarker position={[garageLocation.latitude, garageLocation.longitude]}>
            <Popup>{garageLocation.title || 'Washing Center'}</Popup>
          </LeafletMarker>
        )}

        {trackedUserLocation && (
          <AnimatedTrackedMarker location={trackedUserLocation} />
        )}
        
        <MapBoundsFitter markers={markers} />
      </MapContainer>
      
      {trackedUserLocation?.lastSeenAt ? (
        <View style={styles.badge}>
          <Text style={styles.badgeText}>Last updated {minutesAgo(trackedUserLocation.lastSeenAt)}</Text>
        </View>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { borderRadius: 8, overflow: 'hidden', backgroundColor: '#E5E7EB', marginBottom: 12, position: 'relative' },
  fallback: { borderRadius: 8, backgroundColor: '#E5E7EB', alignItems: 'center', justifyContent: 'center', padding: 16, marginBottom: 12 },
  fallbackText: { color: '#475569', fontWeight: '700', marginTop: 8, textAlign: 'center' },
  badge: { position: 'absolute', left: 10, bottom: 10, backgroundColor: 'rgba(15,23,42,0.78)', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 8, zIndex: 1000 },
  badgeText: { color: '#FFFFFF', fontSize: 12, fontWeight: '700' },
});
