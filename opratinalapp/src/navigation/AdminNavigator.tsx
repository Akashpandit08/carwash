import React from 'react';
import { createDrawerNavigator } from '@react-navigation/drawer';
import { CustomDrawerContent } from './CustomDrawerContent';
import { Header } from '../components/Header';
import { Ionicons } from '@expo/vector-icons';

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

const Drawer = createDrawerNavigator();

const hiddenScreenOptions = { drawerItemStyle: { display: 'none' as const } };

export const AdminNavigator = () => {
  return (
    <Drawer.Navigator
      drawerContent={(props) => <CustomDrawerContent {...props} />}
      screenOptions={{
        header: ({ route, options }) => {
          const title = options.title || route.name;
          const showMenu = !hiddenScreenOptions.drawerItemStyle || !Object.keys(options).includes('drawerItemStyle'); // Rough check: if it's hidden, we probably show back, if it's visible, we show menu.
          // Let's refine: For main menus, showMenu=true. For sub screens, showBack=true.
          const isMainMenu = !options.drawerItemStyle || (options.drawerItemStyle as any).display !== 'none';
          return <Header title={title} showMenu={isMainMenu} showBack={!isMainMenu} />;
        },
        drawerActiveBackgroundColor: '#E3F2FD',
        drawerActiveTintColor: '#007BFF',
        drawerInactiveTintColor: '#333',
        drawerLabelStyle: { fontSize: 16 },
      }}
    >
      <Drawer.Screen name="AdminDashboardScreen" component={AdminDashboardScreen} options={{ title: 'Dashboard', drawerIcon: ({color}) => <Ionicons name="home-outline" size={22} color={color} /> }} />
      <Drawer.Screen name="AdminBookingsScreen" component={AdminBookingsScreen} options={{ title: 'Bookings', drawerIcon: ({color}) => <Ionicons name="calendar-outline" size={22} color={color} /> }} />
      <Drawer.Screen name="AdminPartnersScreen" component={AdminPartnersScreen} options={{ title: 'Partners', drawerIcon: ({color}) => <Ionicons name="people-outline" size={22} color={color} /> }} />
      <Drawer.Screen name="AdminWorkersScreen" component={AdminWorkersScreen} options={{ title: 'Workers', drawerIcon: ({color}) => <Ionicons name="construct-outline" size={22} color={color} /> }} />
      <Drawer.Screen name="AdminPickupDriversScreen" component={AdminPickupDriversScreen} options={{ title: 'Pickup Drivers', drawerIcon: ({color}) => <Ionicons name="car-outline" size={22} color={color} /> }} />
      <Drawer.Screen name="AdminServicesScreen" component={AdminServicesScreen} options={{ title: 'Services', drawerIcon: ({color}) => <Ionicons name="list-outline" size={22} color={color} /> }} />
      <Drawer.Screen name="AdminSubscriptionPlansScreen" component={AdminSubscriptionPlansScreen} options={{ title: 'Subscription Plans', drawerIcon: ({color}) => <Ionicons name="card-outline" size={22} color={color} /> }} />
      <Drawer.Screen name="AdminCustomerSubscriptionsScreen" component={AdminCustomerSubscriptionsScreen} options={{ title: 'Customer Subs', drawerIcon: ({color}) => <Ionicons name="people-circle-outline" size={22} color={color} /> }} />
      <Drawer.Screen name="AdminSlotsScreen" component={AdminSlotsScreen} options={{ title: 'Slots', drawerIcon: ({color}) => <Ionicons name="time-outline" size={22} color={color} /> }} />
      <Drawer.Screen name="AdminCouponsScreen" component={AdminCouponsScreen} options={{ title: 'Coupons', drawerIcon: ({color}) => <Ionicons name="pricetag-outline" size={22} color={color} /> }} />
      <Drawer.Screen name="AdminLocationsScreen" component={AdminLocationsScreen} options={{ title: 'Locations', drawerIcon: ({color}) => <Ionicons name="location-outline" size={22} color={color} /> }} />
      <Drawer.Screen name="AdminCityAdminsScreen" component={AdminCityAdminsScreen} options={{ title: 'City Admins', drawerIcon: ({color}) => <Ionicons name="shield-checkmark-outline" size={22} color={color} /> }} />
      <Drawer.Screen name="AdminPayoutsScreen" component={AdminPayoutsScreen} options={{ title: 'Payouts', drawerIcon: ({color}) => <Ionicons name="cash-outline" size={22} color={color} /> }} />
      <Drawer.Screen name="AdminReportsScreen" component={AdminReportsScreen} options={{ title: 'Reports', drawerIcon: ({color}) => <Ionicons name="pie-chart-outline" size={22} color={color} /> }} />

      {/* Hidden Sub Screens */}
      <Drawer.Screen name="AdminBookingDetailScreen" component={AdminBookingDetailScreen} options={{ title: 'Booking Detail', ...hiddenScreenOptions }} />
      <Drawer.Screen name="AssignTeamScreen" component={AssignTeamScreen} options={{ title: 'Assign Team', ...hiddenScreenOptions }} />
      <Drawer.Screen name="AdminPartnerDetailScreen" component={AdminPartnerDetailScreen} options={{ title: 'Partner Detail', ...hiddenScreenOptions }} />
      <Drawer.Screen name="AdminPartnerFormScreen" component={AdminPartnerFormScreen} options={{ title: 'Manage Partner', ...hiddenScreenOptions }} />
      <Drawer.Screen name="AdminWorkerDetailScreen" component={AdminWorkerDetailScreen} options={{ title: 'Worker Detail', ...hiddenScreenOptions }} />
      <Drawer.Screen name="AdminWorkerFormScreen" component={AdminWorkerFormScreen} options={{ title: 'Manage Worker', ...hiddenScreenOptions }} />
      <Drawer.Screen name="AdminPickupDriverDetailScreen" component={AdminPickupDriverDetailScreen} options={{ title: 'Driver Detail', ...hiddenScreenOptions }} />
      <Drawer.Screen name="AdminPickupDriverFormScreen" component={AdminPickupDriverFormScreen} options={{ title: 'Manage Driver', ...hiddenScreenOptions }} />
      
      <Drawer.Screen name="NotificationsScreen" component={NotificationsScreen} options={{ title: 'Notifications', ...hiddenScreenOptions }} />
    </Drawer.Navigator>
  );
};
