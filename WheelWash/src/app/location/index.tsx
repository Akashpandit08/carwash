import { Ionicons } from '@expo/vector-icons';
import * as Location from 'expo-location';
import { router } from 'expo-router';
import { useState } from 'react';
import { ActivityIndicator, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { cityIdsForName, MUTED, PRIMARY, TEXT } from '@/lib/wheelwash-data';
import { Logo, PrimaryButton } from '@/components/wheelwash/ui';
import { useLocationStore } from '@/store/locationStore';

export default function LocationPermissionScreen() {
  const [loading, setLoading] = useState(false);
  const { saveLocation } = useLocationStore();

  const requestLocation = async () => {
    setLoading(true);
    try {
      const { status } = await Location.requestForegroundPermissionsAsync();

      if (status !== 'granted') {
        router.push('/location/manual');
        return;
      }

      const location = await Location.getCurrentPositionAsync({});
      let address = {};
      try {
        const reverse = await Location.reverseGeocodeAsync({
          latitude: location.coords.latitude,
          longitude: location.coords.longitude,
        });
        if (reverse && reverse.length > 0) {
          address = reverse[0];
        }
      } catch (e) {
        // Native geocoding failed, will use fallback
      }

      let city = address.city || address.subregion || address.district;
      let region = address.region || address.street;
      let pincode = address.postalCode;
      let name = address.name;
      let street = address.street;

      if (!city || city === 'Unknown City') {
        try {
          const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${location.coords.latitude}&lon=${location.coords.longitude}`, {
            headers: { 'User-Agent': 'WheelWashApp/1.0' }
          });
          const data = await response.json();
          if (data && data.address) {
            city = data.address.city || data.address.state_district || data.address.county || city;
            region = data.address.state || region;
            pincode = data.address.postcode || pincode;
            name = data.address.suburb || data.address.neighbourhood || data.name || name;
            street = data.address.road || street;
          }
        } catch (e) {
          // Fallback failed
        }
      }

      city = city || 'Unknown City';
      region = region || 'Unknown Region';
      pincode = pincode || '000000';
      const fullAddress = [name, street, city, region, pincode].filter(Boolean).join(', ') || 'Current Location';

      const userLocation = {
        latitude: location.coords.latitude,
        longitude: location.coords.longitude,
        city,
        region,
        pincode,
        fullAddress,
        ...cityIdsForName(city),
      };

      await saveLocation(userLocation);
      router.replace('/(tabs)');
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.root}>
      <View style={styles.header}>
        <Logo />
      </View>
      <View style={styles.content}>
        <View style={styles.pinCircle}>
          <Ionicons name="location" size={58} color={PRIMARY} />
        </View>
        <Text style={styles.title}>Allow Location Access</Text>
        <Text style={styles.subtitle}>We need your location to show doorstep car wash services near you.</Text>
        <View style={styles.actions}>
          <PrimaryButton title={loading ? 'Finding Location...' : 'Allow Location'} icon="navigate" onPress={requestLocation} />
          {loading && <ActivityIndicator style={styles.loader} color={PRIMARY} />}
          <TouchableOpacity style={styles.manual} onPress={() => router.push('/location/manual')}>
            <Text style={styles.manualText}>Enter Manually</Text>
          </TouchableOpacity>
        </View>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  header: { paddingTop: 24 },
  content: { flex: 1, paddingHorizontal: 28, alignItems: 'center', justifyContent: 'center', paddingBottom: 60 },
  pinCircle: {
    width: 132,
    height: 132,
    borderRadius: 42,
    backgroundColor: '#EAF4FF',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 34,
  },
  title: { color: TEXT, fontSize: 32, fontWeight: '900', textAlign: 'center' },
  subtitle: { marginTop: 14, color: MUTED, fontSize: 18, lineHeight: 28, textAlign: 'center' },
  actions: { width: '100%', marginTop: 42 },
  loader: { marginTop: 14 },
  manual: { minHeight: 54, alignItems: 'center', justifyContent: 'center', marginTop: 12 },
  manualText: { color: PRIMARY, fontSize: 17, fontWeight: '800' },
});
