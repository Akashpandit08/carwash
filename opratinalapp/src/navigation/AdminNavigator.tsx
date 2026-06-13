import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { AdminDashboardScreen } from '../screens/admin/AdminDashboardScreen';
import { AdminBookingsScreen } from '../screens/admin/AdminBookingsScreen';
import { AdminBookingDetailScreen } from '../screens/admin/AdminBookingDetailScreen';
import { AssignTeamScreen } from '../screens/admin/AssignTeamScreen';
import { AdminPartnersScreen } from '../screens/admin/AdminPartnersScreen';
import { AdminPartnerDetailScreen } from '../screens/admin/AdminPartnerDetailScreen';
import { AdminPartnerFormScreen } from '../screens/admin/AdminPartnerFormScreen';
import { AdminWorkersScreen } from '../screens/admin/AdminWorkersScreen';
import { AdminWorkerDetailScreen } from '../screens/admin/AdminWorkerDetailScreen';
import { AdminWorkerFormScreen } from '../screens/admin/AdminWorkerFormScreen';
import { AdminPickupDriversScreen } from '../screens/admin/AdminPickupDriversScreen';
import { AdminPickupDriverDetailScreen } from '../screens/admin/AdminPickupDriverDetailScreen';
import { AdminPickupDriverFormScreen } from '../screens/admin/AdminPickupDriverFormScreen';
import { NotificationsScreen } from '../screens/shared/NotificationsScreen';

// New CRUD Screens
import { AdminServicesScreen } from '../screens/admin/AdminServicesScreen';
import { AdminSubscriptionPlansScreen } from '../screens/admin/AdminSubscriptionPlansScreen';
import { AdminCustomerSubscriptionsScreen } from '../screens/admin/AdminCustomerSubscriptionsScreen';
import { AdminSlotsScreen } from '../screens/admin/AdminSlotsScreen';
import { AdminCouponsScreen } from '../screens/admin/AdminCouponsScreen';
import { AdminLocationsScreen } from '../screens/admin/AdminLocationsScreen';
import { AdminCityAdminsScreen } from '../screens/admin/AdminCityAdminsScreen';
import { AdminPayoutsScreen } from '../screens/admin/AdminPayoutsScreen';
import { AdminReportsScreen } from '../screens/admin/AdminReportsScreen';

const Stack = createNativeStackNavigator();

export const AdminNavigator = () => {
  return (
    <Stack.Navigator>
      <Stack.Screen name="AdminDashboardScreen" component={AdminDashboardScreen} options={{ title: 'Admin Dashboard' }} />
      <Stack.Screen name="AdminBookingsScreen" component={AdminBookingsScreen} options={{ title: 'Bookings' }} />
      <Stack.Screen name="AdminBookingDetailScreen" component={AdminBookingDetailScreen} options={{ title: 'Booking Detail' }} />
      <Stack.Screen name="AssignTeamScreen" component={AssignTeamScreen} options={{ title: 'Assign Team' }} />
      
      {/* Staff & Partners */}
      <Stack.Screen name="AdminPartnersScreen" component={AdminPartnersScreen} options={{ title: 'Partners' }} />
      <Stack.Screen name="AdminPartnerDetailScreen" component={AdminPartnerDetailScreen} options={{ title: 'Partner Detail' }} />
      <Stack.Screen name="AdminPartnerFormScreen" component={AdminPartnerFormScreen} options={{ title: 'Manage Partner' }} />
      <Stack.Screen name="AdminWorkersScreen" component={AdminWorkersScreen} options={{ title: 'Workers' }} />
      <Stack.Screen name="AdminWorkerDetailScreen" component={AdminWorkerDetailScreen} options={{ title: 'Worker Detail' }} />
      <Stack.Screen name="AdminWorkerFormScreen" component={AdminWorkerFormScreen} options={{ title: 'Manage Worker' }} />
      <Stack.Screen name="AdminPickupDriversScreen" component={AdminPickupDriversScreen} options={{ title: 'Pickup Drivers' }} />
      <Stack.Screen name="AdminPickupDriverDetailScreen" component={AdminPickupDriverDetailScreen} options={{ title: 'Driver Detail' }} />
      <Stack.Screen name="AdminPickupDriverFormScreen" component={AdminPickupDriverFormScreen} options={{ title: 'Manage Driver' }} />
      
      {/* Configuration & General */}
      <Stack.Screen name="AdminServicesScreen" component={AdminServicesScreen} options={{ title: 'Services' }} />
      <Stack.Screen name="AdminSubscriptionPlansScreen" component={AdminSubscriptionPlansScreen} options={{ title: 'Subscription Plans' }} />
      <Stack.Screen name="AdminCustomerSubscriptionsScreen" component={AdminCustomerSubscriptionsScreen} options={{ title: 'Customer Subscriptions' }} />
      <Stack.Screen name="AdminSlotsScreen" component={AdminSlotsScreen} options={{ title: 'Slots' }} />
      <Stack.Screen name="AdminCouponsScreen" component={AdminCouponsScreen} options={{ title: 'Coupons' }} />
      <Stack.Screen name="AdminLocationsScreen" component={AdminLocationsScreen} options={{ title: 'Locations' }} />
      <Stack.Screen name="AdminCityAdminsScreen" component={AdminCityAdminsScreen} options={{ title: 'City Admins' }} />
      <Stack.Screen name="AdminPayoutsScreen" component={AdminPayoutsScreen} options={{ title: 'Payouts' }} />
      <Stack.Screen name="AdminReportsScreen" component={AdminReportsScreen} options={{ title: 'Reports' }} />

      <Stack.Screen name="NotificationsScreen" component={NotificationsScreen} options={{ title: 'Notifications', headerShown: false }} />
    </Stack.Navigator>
  );
};
