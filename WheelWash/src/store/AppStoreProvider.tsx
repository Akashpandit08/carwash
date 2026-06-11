import React from 'react';
import { AuthProvider } from './authStore';
import { BookingProvider } from './bookingStore';
import { LocationProvider } from './locationStore';
import { ServiceProvider } from './serviceStore';
import { VehicleProvider } from './vehicleStore';

export function AppStoreProvider({ children }: { children: React.ReactNode }) {
  return (
    <AuthProvider>
      <LocationProvider>
        <VehicleProvider>
          <ServiceProvider>
            <BookingProvider>{children}</BookingProvider>
          </ServiceProvider>
        </VehicleProvider>
      </LocationProvider>
    </AuthProvider>
  );
}
