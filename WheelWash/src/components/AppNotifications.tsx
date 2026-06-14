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
  Notifications.setNotificationHandler({
    handleNotification: async () => ({
      shouldShowBanner: true,
      shouldShowList: true,
      shouldPlaySound: true,
      shouldSetBadge: false,
    }),
  });
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
      if (!Device.isDevice || Platform.OS === 'web') return;


      if (Platform.OS === 'android') {
        await Notifications.setNotificationChannelAsync('default', {
          name: 'Default',
          importance: Notifications.AndroidImportance.MAX,
          vibrationPattern: [0, 250, 250, 250],
          enableVibrate: true,
          lightColor: '#0877F2',
          sound: 'default',
        });
      }

      const existing = await Notifications.getPermissionsAsync();
      if (__DEV__) console.log('Notification permission existing status:', existing.status);
      const permission = existing.status === 'granted'
        ? existing
        : await Notifications.requestPermissionsAsync();
      if (__DEV__) console.log('Notification permission final status:', permission.status);

      if (permission.status !== 'granted' || cancelled) return;

      const projectId = Constants.expoConfig?.extra?.eas?.projectId;
      const tokenResult = await Notifications.getExpoPushTokenAsync(projectId ? { projectId } : undefined);
      if (__DEV__) console.log('Expo push token generated:', tokenResult.data);

      await saveDeviceToken({
        user_id: user.id,
        role: user.role || 'customer',
        device_token: tokenResult.data,
        expo_push_token: tokenResult.data,
        platform: Platform.OS,
        device_type: Platform.OS,
        device_name: Device.deviceName || undefined,
      });
      if (__DEV__) console.log('Device token registered with backend', { user_id: user.id, role: user.role || 'customer' });
    };

    register().catch((error: unknown) => {
      if (__DEV__) console.error('Push registration failed:', error);
    });

    receivedSub = Notifications.addNotificationReceivedListener((notification: any) => {
      if (__DEV__) {
        console.log('Foreground notification received:', {
          title: notification?.request?.content?.title,
          body: notification?.request?.content?.body,
          data: notification?.request?.content?.data,
        });
      }
    });
    responseSub = Notifications.addNotificationResponseReceivedListener((response: any) => {
      const data = response?.notification?.request?.content?.data || {};
      if (__DEV__) console.log('Notification tapped:', data);
      handleNotificationRedirect(data).catch((error) => {
        if (__DEV__) console.error('Notification tap redirect failed:', error);
      });
    });

    if (Platform.OS !== 'web') {
      Notifications.getLastNotificationResponseAsync()
        .then((response: any) => {
          const data = response?.notification?.request?.content?.data;
          if (__DEV__) console.log('Cold-start notification response:', data || null);
          if (data) {
            return handleNotificationRedirect(data);
          }
        })
        .catch((error: unknown) => {
          if (__DEV__) console.error('Cold-start notification handling failed:', error);
        });
    }

    return () => {
      cancelled = true;
      receivedSub?.remove();
      responseSub?.remove();
    };
  }, [user]);

  return null;
}
