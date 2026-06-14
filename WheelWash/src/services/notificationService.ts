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
    const { status: existingStatus } = await Notifications.getPermissionsAsync();
    let finalStatus = existingStatus;

    if (existingStatus !== 'granted') {
      const { status } = await Notifications.requestPermissionsAsync();
      finalStatus = status;
    }

    if (finalStatus !== 'granted') {
      if (__DEV__) console.log('Push notification permission not granted');
      return null;
    }

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
      });
      if (__DEV__) console.log('Device token registered with backend');
    } catch (error) {
      if (__DEV__) console.error('Failed to register device token with backend:', error);
    }

    // Setup Android notification channel
    if (Platform.OS === 'android') {
      await Notifications.setNotificationChannelAsync('default', {
        name: 'Default',
        importance: Notifications.AndroidImportance.MAX,
        vibrationPattern: [0, 250, 250, 250],
        lightColor: '#0877F2',
        sound: 'default',
      });
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
  const subscription = Notifications.addNotificationResponseReceivedListener(
    (response) => {
      const data = response.notification.request.content.data as Record<string, any>;
      const screen = data?.screen;
      const bookingId = data?.booking_id;

      if (__DEV__) console.log('Notification tapped:', { screen, bookingId, data });

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
    }
  );

  return () => subscription.remove();
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
