import React, { useEffect, useRef } from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { AuthNavigator } from './AuthNavigator';
import { RoleRedirectScreen } from '../screens/auth/RoleRedirectScreen';
import { AdminNavigator } from './AdminNavigator';
import { PartnerNavigator } from './PartnerNavigator';
import { WorkerNavigator } from './WorkerNavigator';
import { PickupDriverNavigator } from './PickupDriverNavigator';
import {
  setupNotificationHandler,
  setupNotificationTapListener,
} from '../services/notificationService';

const Stack = createNativeStackNavigator();

// Setup notification handler at module level (before component mounts)
setupNotificationHandler();

export const AppNavigator = () => {
  const navigationRef = useRef<any>(null);

  useEffect(() => {
    // Setup notification tap listener once navigation is ready
    const cleanup = setupNotificationTapListener(navigationRef);
    return cleanup;
  }, []);

  return (
    <NavigationContainer ref={navigationRef}>
      <Stack.Navigator screenOptions={{ headerShown: false }} initialRouteName="RoleRedirectScreen">
        <Stack.Screen name="RoleRedirectScreen" component={RoleRedirectScreen} />
        <Stack.Screen name="Auth" component={AuthNavigator} />
        <Stack.Screen name="Admin" component={AdminNavigator} />
        <Stack.Screen name="Partner" component={PartnerNavigator} />
        <Stack.Screen name="Worker" component={WorkerNavigator} />
        <Stack.Screen name="PickupDriver" component={PickupDriverNavigator} />
      </Stack.Navigator>
    </NavigationContainer>
  );
};
