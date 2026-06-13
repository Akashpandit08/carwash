import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { WorkerDashboardScreen } from '../screens/worker/WorkerDashboardScreen';
import { WorkerJobsScreen } from '../screens/worker/WorkerJobsScreen';
import { WorkerJobDetailScreen } from '../screens/worker/WorkerJobDetailScreen';
import { WorkerExecutionScreen } from '../screens/worker/WorkerExecutionScreen';
import { WorkerEarningsScreen } from '../screens/shared/EarningsScreens';
import { NotificationsScreen } from '../screens/shared/NotificationsScreen';
import { WorkerProfileScreen } from '../screens/worker/WorkerProfileScreen';

const Stack = createNativeStackNavigator();

export const WorkerNavigator = () => {
  return (
    <Stack.Navigator>
      <Stack.Screen name="WorkerDashboardScreen" component={WorkerDashboardScreen} options={{ title: 'Worker Dashboard' }} />
      <Stack.Screen name="WorkerJobsScreen" component={WorkerJobsScreen} options={{ title: 'My Jobs' }} />
      <Stack.Screen name="WorkerJobDetailScreen" component={WorkerJobDetailScreen} options={{ title: 'Job Detail' }} />
      <Stack.Screen name="WorkerExecutionScreen" component={WorkerExecutionScreen} options={{ title: 'Execute Wash' }} />
      <Stack.Screen name="WorkerEarningsScreen" component={WorkerEarningsScreen} options={{ title: 'Earnings' }} />
      <Stack.Screen name="WorkerProfileScreen" component={WorkerProfileScreen} options={{ title: 'Profile' }} />
      <Stack.Screen name="WorkerNotificationsScreen" component={NotificationsScreen} options={{ title: 'Notifications', headerShown: false }} />
      <Stack.Screen name="NotificationsScreen" component={NotificationsScreen} options={{ title: 'Notifications', headerShown: false }} />
    </Stack.Navigator>
  );
};
