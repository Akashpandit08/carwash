import React, { useEffect, useState, useCallback } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import apiClient from '../../api/client';
import { markNotificationRead, markAllNotificationsRead } from '../../services/notificationService';
import { apiErrorMessage, devLog, extractCollection } from '../../utils/apiResponse';

type NotificationItem = {
  id: number;
  notification_id: number;
  user_id: number;
  role: string;
  is_read: boolean;
  read_at: string | null;
  created_at: string;
  notification: {
    id: number;
    title: string;
    message: string;
    body: string | null;
    type: string;
    booking_id: number | null;
    screen: string | null;
    data: Record<string, any> | null;
    created_at: string;
  } | null;
};

export const NotificationsScreen = ({ navigation }: any) => {
  const [notifications, setNotifications] = useState<NotificationItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [userId, setUserId] = useState<number | null>(null);
  const [userRole, setUserRole] = useState<string>('');
  const [error, setError] = useState('');

  const fetchNotifications = useCallback(async () => {
    try {
      const userDataStr = await AsyncStorage.getItem('userData');
      if (!userDataStr) {
        setError('Please log in again to view notifications.');
        return;
      }

      const user = JSON.parse(userDataStr);
      setUserId(user.id);
      setUserRole(user.role);

      const response = await apiClient.get('/app/notifications', {
        params: { user_id: user.id, role: user.role },
      });

      setNotifications(extractCollection(response.data));
      setError('');
    } catch (error) {
      const message = apiErrorMessage(error, 'Could not load notifications.');
      devLog('[Notifications error]', error);
      setError(message);
      setNotifications([]);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    fetchNotifications();
  }, [fetchNotifications]);

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    fetchNotifications();
  }, [fetchNotifications]);

  const handleNotificationTap = async (item: NotificationItem) => {
    // Mark as read
    if (!item.is_read) {
      await markNotificationRead(item.id);
      setNotifications((prev) =>
        prev.map((n) => (n.id === item.id ? { ...n, is_read: true } : n))
      );
    }

    // Navigate based on screen
    const screen = item.notification?.screen || item.notification?.data?.screen;
    const bookingId = item.notification?.booking_id || item.notification?.data?.booking_id;

    if (!screen || !bookingId) return;

    switch (screen) {
      case 'worker_job_detail':
        navigation.navigate('Worker', {
          screen: 'WorkerJobDetailScreen',
          params: { bookingId },
        });
        break;
      case 'driver_job_detail':
        navigation.navigate('PickupDriver', {
          screen: 'PickupDriverJobDetailScreen',
          params: { bookingId },
        });
        break;
      case 'partner_booking_detail':
        navigation.navigate('Partner', {
          screen: 'PartnerJobDetail',
          params: { bookingId },
        });
        break;
      case 'booking_detail':
      case 'booking_tracking':
        navigation.navigate('Admin', {
          screen: 'AdminBookingDetail',
          params: { bookingId },
        });
        break;
    }
  };

  const handleMarkAllRead = async () => {
    if (!userId || !userRole) return;
    await markAllNotificationsRead(userId, userRole);
    setNotifications((prev) => prev.map((n) => ({ ...n, is_read: true })));
  };

  const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString();
  };

  const renderItem = ({ item }: { item: NotificationItem }) => {
    const notification = item.notification;
    if (!notification) return null;

    return (
      <TouchableOpacity
        style={[styles.notificationCard, !item.is_read && styles.unreadCard]}
        onPress={() => handleNotificationTap(item)}
        activeOpacity={0.7}
      >
        <View style={styles.notificationContent}>
          <View style={styles.headerRow}>
            <Text style={[styles.title, !item.is_read && styles.unreadTitle]} numberOfLines={1}>
              {notification.title}
            </Text>
            {!item.is_read && <View style={styles.unreadDot} />}
          </View>
          <Text style={styles.body} numberOfLines={2}>
            {notification.body || notification.message}
          </Text>
          <View style={styles.metaRow}>
            <Text style={styles.time}>{formatTime(item.created_at)}</Text>
            {notification.booking_id && (
              <Text style={styles.bookingTag}>Booking #{notification.booking_id}</Text>
            )}
          </View>
        </View>
      </TouchableOpacity>
    );
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#2196F3" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backButton}>
          <Text style={styles.backText}>← Back</Text>
        </TouchableOpacity>
        <Text style={styles.screenTitle}>Notifications</Text>
        {notifications.some((n) => !n.is_read) && (
          <TouchableOpacity onPress={handleMarkAllRead} style={styles.markAllButton}>
            <Text style={styles.markAllText}>Read All</Text>
          </TouchableOpacity>
        )}
      </View>

      <FlatList
        data={notifications}
        renderItem={renderItem}
        keyExtractor={(item) => String(item.id)}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#2196F3']} />
        }
        contentContainerStyle={notifications.length === 0 ? styles.emptyContainer : styles.listContent}
        ListEmptyComponent={
          <View style={styles.emptyState}>
            <Text style={styles.emptyTitle}>{error ? 'Unable to Load Notifications' : 'No Notifications'}</Text>
            <Text style={styles.emptySubtitle}>{error || "You're all caught up!"}</Text>
            {error ? (
              <TouchableOpacity style={styles.retryButton} onPress={fetchNotifications}>
                <Text style={styles.retryText}>Retry</Text>
              </TouchableOpacity>
            ) : null}
          </View>
        }
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F7FA',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F5F7FA',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingTop: 50,
    paddingBottom: 16,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E8ECF0',
  },
  backButton: {
    paddingVertical: 4,
    paddingRight: 12,
  },
  backText: {
    fontSize: 16,
    color: '#2196F3',
    fontWeight: '500',
  },
  screenTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1A1A2E',
    flex: 1,
    textAlign: 'center',
  },
  markAllButton: {
    paddingVertical: 4,
    paddingLeft: 12,
  },
  markAllText: {
    fontSize: 14,
    color: '#2196F3',
    fontWeight: '600',
  },
  listContent: {
    padding: 16,
  },
  notificationCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
    marginBottom: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.06,
    shadowRadius: 4,
    elevation: 2,
  },
  unreadCard: {
    backgroundColor: '#EDF5FF',
    borderLeftWidth: 3,
    borderLeftColor: '#2196F3',
  },
  notificationContent: {
    flex: 1,
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 6,
  },
  title: {
    fontSize: 15,
    fontWeight: '600',
    color: '#333',
    flex: 1,
  },
  unreadTitle: {
    color: '#1A1A2E',
    fontWeight: '700',
  },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: '#2196F3',
    marginLeft: 8,
  },
  body: {
    fontSize: 13,
    color: '#666',
    lineHeight: 18,
    marginBottom: 8,
  },
  metaRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  time: {
    fontSize: 12,
    color: '#999',
  },
  bookingTag: {
    fontSize: 11,
    color: '#2196F3',
    backgroundColor: '#E3F2FD',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 10,
    overflow: 'hidden',
    fontWeight: '500',
  },
  emptyContainer: {
    flexGrow: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyState: {
    alignItems: 'center',
    paddingVertical: 60,
  },
  emptyIcon: {
    fontSize: 48,
    marginBottom: 16,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#333',
    marginBottom: 6,
  },
  emptySubtitle: {
    fontSize: 14,
    color: '#999',
    textAlign: 'center',
  },
  retryButton: {
    marginTop: 18,
    backgroundColor: '#2196F3',
    borderRadius: 8,
    paddingHorizontal: 18,
    paddingVertical: 10,
  },
  retryText: {
    color: '#FFFFFF',
    fontWeight: '700',
  },
});
