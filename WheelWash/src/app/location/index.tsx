import { Ionicons } from '@expo/vector-icons';
import * as Location from 'expo-location';
import { router } from 'expo-router';
import { useState } from 'react';
import { ActivityIndicator, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MUTED, PRIMARY, TEXT } from '@/lib/wheelwash-data';
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
      const reverse = await Location.reverseGeocodeAsync({
        latitude: location.coords.latitude,
        longitude: location.coords.longitude,
      });

      const address = reverse[0] || {};

      const userLocation = {
        latitude: location.coords.latitude,
        longitude: location.coords.longitude,
        city: address.city || address.subregion || '',
        region: address.region || '',
        pincode: address.postalCode || '',
        fullAddress: `${address.name || ''}, ${address.city || ''}, ${address.region || ''}`,
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
