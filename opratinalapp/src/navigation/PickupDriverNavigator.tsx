import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { DriverDashboardScreen } from '../screens/pickupDriver/DriverDashboardScreen';
import { DriverJobsScreen } from '../screens/pickupDriver/DriverJobsScreen';
import { DriverJobDetailScreen } from '../screens/pickupDriver/DriverJobDetailScreen';
import { PickupExecutionScreen } from '../screens/pickupDriver/PickupExecutionScreen';
import { DeliveryExecutionScreen } from '../screens/pickupDriver/DeliveryExecutionScreen';
import { DriverEarningsScreen } from '../screens/shared/EarningsScreens';
import { NotificationsScreen } from '../screens/shared/NotificationsScreen';
import { PickupDriverProfileScreen } from '../screens/pickupDriver/PickupDriverProfileScreen';

const Stack = createNativeStackNavigator();

export const PickupDriverNavigator = () => {
  return (
    <Stack.Navigator>
      <Stack.Screen name="DriverDashboardScreen" component={DriverDashboardScreen} options={{ title: 'Driver Dashboard' }} />
      <Stack.Screen name="PickupDriverDashboardScreen" component={DriverDashboardScreen} options={{ title: 'Driver Dashboard' }} />
      <Stack.Screen name="DriverJobsScreen" component={DriverJobsScreen} options={{ title: 'My Pickups/Deliveries' }} />
      <Stack.Screen name="PickupDriverJobsScreen" component={DriverJobsScreen} options={{ title: 'My Pickups/Deliveries' }} />
      <Stack.Screen name="DriverJobDetailScreen" component={DriverJobDetailScreen} options={{ title: 'Job Detail' }} />
      <Stack.Screen name="PickupDriverJobDetailScreen" component={DriverJobDetailScreen} options={{ title: 'Job Detail' }} />
      <Stack.Screen name="PickupExecutionScreen" component={PickupExecutionScreen} options={{ title: 'Pickup Process' }} />
      <Stack.Screen name="DeliveryExecutionScreen" component={DeliveryExecutionScreen} options={{ title: 'Delivery Process' }} />
      <Stack.Screen name="DriverEarningsScreen" component={DriverEarningsScreen} options={{ title: 'Earnings' }} />
      <Stack.Screen name="PickupDriverEarningsScreen" component={DriverEarningsScreen} options={{ title: 'Earnings' }} />
      <Stack.Screen name="PickupDriverProfileScreen" component={PickupDriverProfileScreen} options={{ title: 'Profile' }} />
      <Stack.Screen name="PickupDriverNotificationsScreen" component={NotificationsScreen} options={{ title: 'Notifications', headerShown: false }} />
      <Stack.Screen name="NotificationsScreen" component={NotificationsScreen} options={{ title: 'Notifications', headerShown: false }} />
    </Stack.Navigator>
  );
};
