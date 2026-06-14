import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { Platform } from 'react-native';
import { apiClient } from '@/api/client';

const EAS_PROJECT_ID = process.env.EXPO_PUBLIC_EAS_PROJECT_ID || '9dcf48e1-e287-4dda-98fb-5c45aabb6c0c';

/**
 * Setup notification handler for foreground notifications.
 * Call this once at app startup.
 */
export function setupNotificationHandler(): void {
  Notifications.setNotificationHandler({
    handleNotification: async () => ({
      shouldShowBanner: true,
      shouldShowList: true,
      shouldPlaySound: true,
      shouldSetBadge: false,
    }),
  });
}

/**
 * Register for push notifications and send the token to the backend.
 *
 * @param userId - The logged-in user's ID
 * @param role - The user's role (customer)
 */
export async function registerForPushNotifications(
  userId: number | string,
  role: string = 'customer'
): Promise<string | null> {
  if (!Device.isDevice) {
    if (__DEV__) console.log('Push notifications require a physical device');
    return null;
  }

  try {
    // Request permission
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

    const { status: existingStatus } = await Notifications.getPermissionsAsync();
    if (__DEV__) console.log('Notification permission existing status:', existingStatus);
    let finalStatus = existingStatus;

    if (existingStatus !== 'granted') {
      const { status } = await Notifications.requestPermissionsAsync();
      finalStatus = status;
    }

    if (finalStatus !== 'granted') {
      if (__DEV__) console.log('Push notification permission not granted');
      return null;
    }
    if (__DEV__) console.log('Notification permission final status:', finalStatus);

    // Get Expo push token
    const tokenData = await Notifications.getExpoPushTokenAsync({
      projectId: EAS_PROJECT_ID,
    });
    const pushToken = tokenData.data;

    if (__DEV__) console.log('Expo push token:', pushToken);

    // Send token to backend
    try {
      await apiClient.post('/app/device-token', {
        user_id: userId,
        role: role,
        device_token: pushToken,
        expo_push_token: pushToken,
        platform: Platform.OS,
        device_type: Platform.OS,
        device_name: Device.deviceName || undefined,
      });
      if (__DEV__) console.log('Device token registered with backend');
    } catch (error) {
      if (__DEV__) console.error('Failed to register device token with backend:', error);
    }

    return pushToken;
  } catch (error) {
    if (__DEV__) console.error('Error registering for push notifications:', error);
    return null;
  }
}

/**
 * Setup notification tap listener for navigation.
 * Call this with the expo-router router.
 *
 * @param router - The expo-router router instance
 */
export function setupNotificationTapListener(router: any): () => void {
  const routeFromData = (data: Record<string, any>, source: string) => {
    const screen = data?.screen;
    const bookingId = data?.booking_id || data?.job_id;

    if (__DEV__) console.log(`${source} notification response:`, { screen, bookingId, data });

    if (!screen) return;

    switch (screen) {
      case 'booking_tracking':
        if (bookingId) {
          router.push(`/track?id=${bookingId}`);
        }
        break;

      case 'booking_detail':
        if (bookingId) {
          router.push(`/booking-detail?id=${bookingId}`);
        }
        break;

      case 'payment':
        if (bookingId) {
          router.push(`/payment?booking_id=${bookingId}`);
        }
        break;

      default:
        if (__DEV__) console.log('Unknown notification screen:', screen);
        break;
    }
  };

  const receivedSubscription = Notifications.addNotificationReceivedListener((notification) => {
    if (__DEV__) {
      console.log('Foreground notification received:', {
        title: notification.request.content.title,
        body: notification.request.content.body,
        data: notification.request.content.data,
      });
    }
  });

  const subscription = Notifications.addNotificationResponseReceivedListener(
    (response) => {
      const data = response.notification.request.content.data as Record<string, any>;
      routeFromData(data, 'Tapped');
    }
  );

  Notifications.getLastNotificationResponseAsync()
    .then((response) => {
      const data = response?.notification.request.content.data as Record<string, any> | undefined;
      if (__DEV__) console.log('Cold-start notification response:', data || null);
      if (data) routeFromData(data, 'Cold-start');
    })
    .catch((error) => {
      if (__DEV__) console.error('Failed to read cold-start notification response:', error);
    });

  return () => {
    receivedSubscription.remove();
    subscription.remove();
  };
}

/**
 * Get unread notification count.
 */
export async function getUnreadCount(
  userId: number | string,
  role: string = 'customer'
): Promise<number> {
  try {
    const response = await apiClient.get('/app/notifications', {
      params: { user_id: userId, role },
    });
    const notifications = response.data?.data?.data || [];
    return notifications.filter((n: any) => !n.is_read).length;
  } catch {
    return 0;
  }
}

/**
 * Mark a notification as read.
 */
export async function markNotificationRead(notificationId: number | string): Promise<void> {
  try {
    await apiClient.post(`/app/notifications/${notificationId}/read`);
  } catch (error) {
    if (__DEV__) console.error('Failed to mark notification as read:', error);
  }
}

/**
 * Mark all notifications as read.
 */
export async function markAllNotificationsRead(
  userId: number | string,
  role: string = 'customer'
): Promise<void> {
  try {
    await apiClient.post('/app/notifications/read-all', {
      user_id: userId,
      role,
    });
  } catch (error) {
    if (__DEV__) console.error('Failed to mark all notifications as read:', error);
  }
}
