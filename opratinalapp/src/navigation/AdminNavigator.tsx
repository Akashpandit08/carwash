import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { AdminDashboardScreen } from '../screens/admin/AdminDashboardScreen';
import { AdminBookingsScreen } from '../screens/admin/AdminBookingsScreen';
import { AdminBookingDetailScreen } from '../screens/admin/AdminBookingDetailScreen';
import { AssignTeamScreen } from '../screens/admin/AssignTeamScreen';
import { AdminPartnersScreen, AdminWorkersScreen, AdminDriversScreen, AdminServicesScreen, AdminSlotsScreen, AdminCouponsScreen, AdminReportsScreen } from '../screens/admin/Phase2Screens';
import { NotificationsScreen } from '../screens/shared/NotificationsScreen';

const Stack = createNativeStackNavigator();

export const AdminNavigator = () => {
  return (
    <Stack.Navigator>
      <Stack.Screen name="AdminDashboardScreen" component={AdminDashboardScreen} options={{ title: 'Admin Dashboard' }} />
      <Stack.Screen name="AdminBookingsScreen" component={AdminBookingsScreen} options={{ title: 'Bookings' }} />
      <Stack.Screen name="AdminBookingDetailScreen" component={AdminBookingDetailScreen} options={{ title: 'Booking Detail' }} />
      <Stack.Screen name="AssignTeamScreen" component={AssignTeamScreen} options={{ title: 'Assign Team' }} />
      
      {/* Phase 2 Screens */}
      <Stack.Screen name="AdminPartnersScreen" component={AdminPartnersScreen} options={{ title: 'Partners' }} />
      <Stack.Screen name="AdminWorkersScreen" component={AdminWorkersScreen} options={{ title: 'Workers' }} />
      <Stack.Screen name="AdminDriversScreen" component={AdminDriversScreen} options={{ title: 'Drivers' }} />
      <Stack.Screen name="AdminServicesScreen" component={AdminServicesScreen} options={{ title: 'Services' }} />
      <Stack.Screen name="AdminSlotsScreen" component={AdminSlotsScreen} options={{ title: 'Slots' }} />
      <Stack.Screen name="AdminCouponsScreen" component={AdminCouponsScreen} options={{ title: 'Coupons' }} />
      <Stack.Screen name="AdminReportsScreen" component={AdminReportsScreen} options={{ title: 'Reports' }} />
      <Stack.Screen name="NotificationsScreen" component={NotificationsScreen} options={{ title: 'Notifications', headerShown: false }} />
    </Stack.Navigator>
  );
};
