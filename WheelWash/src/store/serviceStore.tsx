import AsyncStorage from '@react-native-async-storage/async-storage';
import React, { createContext, useCallback, useContext, useMemo, useState } from 'react';
import { listServices, ServiceDto } from '@/api/serviceApi';
import { STORAGE_KEYS } from '@/lib/wheelwash-data';

type ServiceState = {
  services: ServiceDto[];
  selectedService: ServiceDto | null;
  loading: boolean;
  error: string | null;
  loadServices: () => Promise<void>;
  selectService: (service: ServiceDto) => Promise<void>;
};

const ServiceContext = createContext<ServiceState | null>(null);

export function ServiceProvider({ children }: { children: React.ReactNode }) {
  const [services, setServices] = useState<ServiceDto[]>([]);
  const [selectedService, setSelectedService] = useState<ServiceDto | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadServices = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const remote = await listServices();
      setServices(remote);
      const stored = await AsyncStorage.getItem(STORAGE_KEYS.selectedService);
      const parsed = stored ? JSON.parse(stored) as ServiceDto : null;
      const selected = remote.find((item) => String(item.id) === String(parsed?.id)) || remote[0] || parsed || null;
      setSelectedService(selected);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Service load failed.');
    } finally {
      setLoading(false);
    }
  }, []);

  const selectService = useCallback(async (service: ServiceDto) => {
    await AsyncStorage.setItem(STORAGE_KEYS.selectedService, JSON.stringify(service));
    setSelectedService(service);
  }, []);

  const value = useMemo(() => ({ services, selectedService, loading, error, loadServices, selectService }), [services, selectedService, loading, error, loadServices, selectService]);
  return <ServiceContext.Provider value={value}>{children}</ServiceContext.Provider>;
}

export function useServiceStore() {
  const context = useContext(ServiceContext);
  if (!context) throw new Error('useServiceStore must be used inside ServiceProvider');
  return context;
}
