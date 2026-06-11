import React, { createContext, useCallback, useContext, useMemo, useState } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { createAddress } from '@/api/customerApi';
import { STORAGE_KEYS, UserLocation } from '@/lib/wheelwash-data';
import { getLocation, saveLocation as persistLocation } from '@/lib/wheelwash-storage';

type LocationState = {
  location: UserLocation | null;
  loading: boolean;
  error: string | null;
  loadLocation: () => Promise<void>;
  saveLocation: (location: UserLocation, syncBackend?: boolean) => Promise<void>;
};

const LocationContext = createContext<LocationState | null>(null);

export function LocationProvider({ children }: { children: React.ReactNode }) {
  const [location, setLocation] = useState<UserLocation | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadLocation = useCallback(async () => {
    setLocation(await getLocation());
  }, []);

  const saveLocation = useCallback(async (next: UserLocation, syncBackend = true) => {
    setLoading(true);
    setError(null);
    try {
      await persistLocation(next);
      setLocation(next);
      if (syncBackend) {
        try {
          const address = await createAddress(next) as { id?: string | number };
          if (address?.id) {
            const withAddressId = { ...next, id: address.id };
            await persistLocation(withAddressId);
            await AsyncStorage.setItem(STORAGE_KEYS.addressId, String(address.id));
            setLocation(withAddressId);
          }
        } catch {
          // Location is still valid locally if address sync fails.
        }
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Location save failed.');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  const value = useMemo(() => ({ location, loading, error, loadLocation, saveLocation }), [location, loading, error, loadLocation, saveLocation]);
  return <LocationContext.Provider value={value}>{children}</LocationContext.Provider>;
}

export function useLocationStore() {
  const context = useContext(LocationContext);
  if (!context) throw new Error('useLocationStore must be used inside LocationProvider');
  return context;
}
