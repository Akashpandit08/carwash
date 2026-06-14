import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import Constants from 'expo-constants';
import { Platform } from 'react-native';
import apiClient from '../api/client';

const EAS_PROJECT_ID = process.env.EXPO_PUBLIC_EAS_PROJECT_ID || 'ae4d3bfe-9c71-46db-b043-991e77d88f55';

// Check for Expo Go using appOwnership (or fallback to executionEnvironment)
const isExpoGo = Constants.appOwnership === 'expo' || Constants.executionEnvironment === 'storeClient';

/**
 * Setup notification handler for foreground notifications.
 * Call this once at app startup.
 */
export function setupNotificationHandler(): void {
  try {
    if (Platform.OS === 'android' && isExpoGo) {
      if (__DEV__) console.log('Push notifications skipped in Expo Go. Use development build for push testing.');
      return;
    }
    
    Notifications.setNotificationHandler({
      handleNotification: async () => ({
        shouldShowBanner: true,
        shouldShowList: true,
        shouldPlaySound: true,
        shouldSetBadge: false,
      }),
    });
  } catch (error) {
    if (__DEV__) console.warn('Failed to set notification handler:', error);
  }
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
  try {
    if (isExpoGo) {
      if (__DEV__) console.log('Push notifications skipped in Expo Go. Use development build for push testing.');
      return null;
    }

    if (!Device.isDevice || Platform.OS === 'web') {
      if (__DEV__) console.log('Push notifications require a physical device and are not currently configured for web');
      return null;
    }

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
        device_type: Platform.OS,
        device_name: Device.deviceName || undefined,
      });
      if (__DEV__) console.log('Device token registered with backend', { userId, role });
    } catch (error) {
      if (__DEV__) console.error('Failed to register device token with backend:', error);
    }

    return pushToken;
  } catch (error) {
    if (__DEV__) console.warn('Push notification registration failed:', error);
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
  try {
    if (Platform.OS === 'android' && isExpoGo) {
      return () => {};
    }

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

    if (Platform.OS !== 'web') {
      Notifications.getLastNotificationResponseAsync()
        .then((response) => routeFromResponse(response, 'Cold-start'))
        .catch((error) => {
          if (__DEV__) console.warn('Failed to read cold-start notification response:', error);
        });
    }

    return () => {
      receivedSubscription.remove();
      responseSubscription.remove();
    };
  } catch (error) {
    if (__DEV__) console.warn('Failed to setup notification tap listener:', error);
    return () => {};
  }
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
    if (__DEV__) console.warn('Failed to mark notification as read:', error);
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
    if (__DEV__) console.warn('Failed to mark all notifications as read:', error);
  }
}
