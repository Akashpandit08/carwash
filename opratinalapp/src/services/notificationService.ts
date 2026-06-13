import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { Platform } from 'react-native';
import apiClient from '../api/client';

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
    console.log('Push notifications require a physical device and are not currently configured for web');
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
      console.log('Push notification permission not granted');
      return null;
    }

    // Get Expo push token
    const tokenData = await Notifications.getExpoPushTokenAsync({
      projectId: undefined,
    });
    const pushToken = tokenData.data;

    console.log('Expo push token:', pushToken);

    // Send token to backend
    try {
      await apiClient.post('/app/device-token', {
        user_id: userId,
        role: role,
        device_token: pushToken,
        platform: Platform.OS,
      });
      console.log('Device token registered with backend');
    } catch (error) {
      console.error('Failed to register device token with backend:', error);
    }

    // Setup Android notification channel
    if (Platform.OS === 'android') {
      await Notifications.setNotificationChannelAsync('default', {
        name: 'Default',
        importance: Notifications.AndroidImportance.MAX,
        vibrationPattern: [0, 250, 250, 250],
        lightColor: '#2196F3',
        sound: 'default',
      });
    }

    return pushToken;
  } catch (error) {
    console.error('Error registering for push notifications:', error);
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
  const subscription = Notifications.addNotificationResponseReceivedListener(
    (response) => {
      const data = response.notification.request.content.data as Record<string, any>;
      const screen = data?.screen;
      const bookingId = data?.booking_id;

      console.log('Notification tapped:', { screen, bookingId, data });

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
          // Admin can view booking detail
          navigation.navigate('Admin', {
            screen: 'AdminBookingDetailScreen',
            params: { bookingId },
          });
          break;

        default:
          console.log('Unknown notification screen:', screen);
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
    console.error('Failed to mark notification as read:', error);
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
    console.error('Failed to mark all notifications as read:', error);
  }
}
