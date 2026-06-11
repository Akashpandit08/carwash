import Constants from 'expo-constants';
import * as Device from 'expo-device';
import { useEffect } from 'react';
import { Platform } from 'react-native';
import { saveDeviceToken } from '@/api/customerApi';
import { handleNotificationRedirect } from '@/lib/navigationRedirect';
import { useAuthStore } from '@/store/authStore';

let Notifications: any = null;

try {
  Notifications = require('expo-notifications');
} catch {
  Notifications = null;
}

export function AppNotifications() {
  const { user } = useAuthStore();

  useEffect(() => {
    if (!Notifications || !user) return;

    let receivedSub: { remove: () => void } | undefined;
    let responseSub: { remove: () => void } | undefined;
    let cancelled = false;

    const register = async () => {
      if (!Device.isDevice) return;

      if (Platform.OS === 'android') {
        await Notifications.setNotificationChannelAsync('default', {
          name: 'default',
          importance: Notifications.AndroidImportance.MAX,
        });
      }

      const existing = await Notifications.getPermissionsAsync();
      const permission = existing.status === 'granted'
        ? existing
        : await Notifications.requestPermissionsAsync();

      if (permission.status !== 'granted' || cancelled) return;

      const projectId = Constants.expoConfig?.extra?.eas?.projectId;
      const tokenResult = await Notifications.getExpoPushTokenAsync(projectId ? { projectId } : undefined);

      await saveDeviceToken({
        expo_push_token: tokenResult.data,
        device_type: Platform.OS,
        device_name: Device.deviceName || undefined,
      });
    };

    register().catch(() => undefined);

    receivedSub = Notifications.addNotificationReceivedListener(() => undefined);
    responseSub = Notifications.addNotificationResponseReceivedListener((response: any) => {
      handleNotificationRedirect(response?.notification?.request?.content?.data || {}).catch(() => undefined);
    });

    return () => {
      cancelled = true;
      receivedSub?.remove();
      responseSub?.remove();
    };
  }, [user]);

  return null;
}
