import AsyncStorage from '@react-native-async-storage/async-storage';
import { Redirect } from 'expo-router';
import { useEffect, useRef, useState } from 'react';
import { ActivityIndicator, StyleSheet, View } from 'react-native';
import { PRIMARY, STORAGE_KEYS } from '@/lib/wheelwash-data';
import { useAuthStore } from '@/store/authStore';

type BootTarget = '/login' | '/location' | '/(tabs)' | null;

export default function Index() {
  const { hydrate } = useAuthStore();
  const [target, setTarget] = useState<BootTarget>(null);
  const mountedRef = useRef(true);

  useEffect(() => {
    mountedRef.current = true;

    const boot = async () => {
      await hydrate();
      const [[, token], [, location]] = await AsyncStorage.multiGet([
        STORAGE_KEYS.customerToken,
        STORAGE_KEYS.location,
      ]);

      if (!mountedRef.current) return;

      if (!token) {
        setTarget('/login');
      } else if (!location) {
        setTarget('/location');
      } else {
        setTarget('/(tabs)');
      }
    };

    boot();

    return () => {
      mountedRef.current = false;
    };
  }, [hydrate]);

  if (target) {
    return <Redirect href={target} />;
  }

  return (
    <View style={styles.root}>
      <ActivityIndicator color={PRIMARY} size="large" />
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: '#fff' },
});
