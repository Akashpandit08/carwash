import AsyncStorage from '@react-native-async-storage/async-storage';
import React, { createContext, useCallback, useContext, useMemo, useState } from 'react';
import { CustomerUser, getMe, logout as logoutApi, verifyOtp } from '@/api/authApi';
import { STORAGE_KEYS } from '@/lib/wheelwash-data';

type AuthState = {
  token: string | null;
  user: CustomerUser | null;
  hydrated: boolean;
  hydrate: () => Promise<void>;
  login: (mobileNumber: string, otp: string) => Promise<void>;
  logout: () => Promise<void>;
};

const AuthContext = createContext<AuthState | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [token, setToken] = useState<string | null>(null);
  const [user, setUser] = useState<CustomerUser | null>(null);
  const [hydrated, setHydrated] = useState(false);

  const hydrate = useCallback(async () => {
    const [[, storedToken], [, storedUser]] = await AsyncStorage.multiGet([STORAGE_KEYS.customerToken, STORAGE_KEYS.user]);
    setToken(storedToken);
    setUser(storedUser ? JSON.parse(storedUser) : null);
    if (storedToken) {
      try {
        const freshUser = await getMe();
        setUser(freshUser);
        await AsyncStorage.setItem(STORAGE_KEYS.user, JSON.stringify(freshUser));
      } catch {
        await AsyncStorage.multiRemove([STORAGE_KEYS.customerToken, STORAGE_KEYS.user]);
        setToken(null);
        setUser(null);
      }
    }
    setHydrated(true);
  }, []);

  const login = useCallback(async (mobileNumber: string, otp: string) => {
    const result = await verifyOtp(mobileNumber, otp);
    await AsyncStorage.multiSet([
      [STORAGE_KEYS.customerToken, result.token],
      [STORAGE_KEYS.user, JSON.stringify(result.user)],
    ]);
    setToken(result.token);
    setUser(result.user);
  }, []);

  const logout = useCallback(async () => {
    try {
      if (token) await logoutApi();
    } finally {
      await AsyncStorage.multiRemove([
        STORAGE_KEYS.customerToken,
        STORAGE_KEYS.user,
        STORAGE_KEYS.location,
        STORAGE_KEYS.selectedVehicle,
        STORAGE_KEYS.selectedService,
        STORAGE_KEYS.bookingId,
      ]);
      setToken(null);
      setUser(null);
    }
  }, [token]);

  const value = useMemo(() => ({ token, user, hydrated, hydrate, login, logout }), [token, user, hydrated, hydrate, login, logout]);
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuthStore() {
  const context = useContext(AuthContext);
  if (!context) throw new Error('useAuthStore must be used inside AuthProvider');
  return context;
}
