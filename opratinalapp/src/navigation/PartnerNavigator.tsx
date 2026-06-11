import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { PartnerDashboardScreen } from '../screens/partner/PartnerDashboardScreen';
import { PartnerJobsScreen } from '../screens/partner/PartnerJobsScreen';
import { PartnerJobDetailScreen } from '../screens/partner/PartnerJobDetailScreen';
import { PartnerWorkersScreen } from '../screens/partner/PartnerWorkersScreen';
import { PartnerEarningsScreen } from '../screens/shared/EarningsScreens';
import { NotificationsScreen } from '../screens/shared/NotificationsScreen';

const Stack = createNativeStackNavigator();

export const PartnerNavigator = () => {
  return (
    <Stack.Navigator>
      <Stack.Screen name="PartnerDashboardScreen" component={PartnerDashboardScreen} options={{ title: 'Partner Dashboard' }} />
      <Stack.Screen name="PartnerJobsScreen" component={PartnerJobsScreen} options={{ title: 'My Jobs' }} />
      <Stack.Screen name="PartnerJobDetailScreen" component={PartnerJobDetailScreen} options={{ title: 'Job Detail' }} />
      <Stack.Screen name="PartnerWorkersScreen" component={PartnerWorkersScreen} options={{ title: 'My Workers' }} />
      <Stack.Screen name="PartnerEarningsScreen" component={PartnerEarningsScreen} options={{ title: 'Earnings' }} />
      <Stack.Screen name="NotificationsScreen" component={NotificationsScreen} options={{ title: 'Notifications', headerShown: false }} />
    </Stack.Navigator>
  );
};
