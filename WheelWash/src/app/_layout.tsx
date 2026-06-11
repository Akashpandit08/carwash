import React from 'react';
import { StatusBar } from 'react-native';
import { Stack } from 'expo-router';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { AppStoreProvider } from '@/store/AppStoreProvider';
import { AppNotifications } from '@/components/AppNotifications';

export default function RootLayout() {
  return (
    <GestureHandlerRootView style={{ flex: 1 }}>
      <SafeAreaProvider>
        <AppStoreProvider>
          <AppNotifications />
          <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />
          <Stack screenOptions={{ headerShown: false }} />
        </AppStoreProvider>
      </SafeAreaProvider>
    </GestureHandlerRootView>
  );
}
