import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { Platform } from 'react-native';
import apiClient from '../api/client';

const EAS_PROJECT_ID = process.env.EXPO_PUBLIC_EAS_PROJECT_ID || 'ae4d3bfe-9c71-46db-b043-991e77d88f55';

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
 * @param role - The user's role (partner, worker, pickup_driver, admin)
 */
export async function registerForPushNotifications(
  userId: number | string,
  role: string
): Promise<string | null> {
  if (!Device.isDevice || Platform.OS === 'web') {
    if (__DEV__) console.log('Push notifications require a physical device and are not currently configured for web');
    return null;
  }

  try {
    if (Platform.OS === 'android') {
      await Notifications.setNotificationChannelAsync('default', {
        name: 'Default',
        importance: Notifications.AndroidImportance.MAX,
        vibrationPattern: [0, 250, 250, 250],
        enableVibrate: true,
        lightColor: '#2196F3',
        sound: 'default',
      });
    }

    // Request permission
    const { status: existingStatus } = await Notifications.getPermissionsAsync();
    if (__DEV__) console.log('Notification permission existing status:', existingStatus);
    let finalStatus = existingStatus;

    if (existingStatus !== 'granted') {
      const { status } = await Notifications.requestPermissionsAsync();
      finalStatus = status;
    }
    if (__DEV__) console.log('Notification permission final status:', finalStatus);

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
        device_type: Platform.OS,
        device_name: Device.deviceName || undefined,
      });
      if (__DEV__) console.log('Device token registered with backend', { userId, role });
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
 * Setup notification tap listener for React Navigation.
 * Call this with a navigation ref.
 *
 * @param navigationRef - React Navigation ref
 */
export function setupNotificationTapListener(navigationRef: any): () => void {
  const routeFromResponse = (response: Notifications.NotificationResponse | null, source: string) => {
    if (!response) return;

    const data = response.notification.request.content.data as Record<string, any>;
    const screen = data?.screen;
    const bookingId = data?.booking_id || data?.job_id;

    if (__DEV__) console.log(`${source} notification response:`, { screen, bookingId, data });

    if (!screen || !navigationRef?.current) return;

    const navigation = navigationRef.current;

    switch (screen) {
      case 'worker_job_detail':
        navigation.navigate('Worker', {
          screen: 'WorkerJobDetailScreen',
          params: { bookingId },
        });
        break;

      case 'driver_job_detail':
        navigation.navigate('PickupDriver', {
          screen: 'DriverJobDetailScreen',
          params: { bookingId },
        });
        break;

      case 'partner_booking_detail':
        navigation.navigate('Partner', {
          screen: 'PartnerJobDetailScreen',
          params: { bookingId },
        });
        break;

      case 'booking_tracking':
      case 'booking_detail':
        navigation.navigate('Admin', {
          screen: 'AdminBookingDetailScreen',
          params: { bookingId },
        });
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

  const responseSubscription = Notifications.addNotificationResponseReceivedListener((response) => {
    routeFromResponse(response, 'Tapped');
  });

  Notifications.getLastNotificationResponseAsync()
    .then((response) => routeFromResponse(response, 'Cold-start'))
    .catch((error) => {
      if (__DEV__) console.error('Failed to read cold-start notification response:', error);
    });

  return () => {
    receivedSubscription.remove();
    responseSubscription.remove();
  };
}

/**
 * Get unread notification count.
 */
export async function getUnreadCount(
  userId: number | string,
  role: string
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
  role: string
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
