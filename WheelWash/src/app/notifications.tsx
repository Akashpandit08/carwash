import React, { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, RefreshControl, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import { listNotifications, markAllNotificationsRead, markNotificationRead } from '@/api/customerApi';
import { ApiError } from '@/api/client';
import { handleNotificationRedirect } from '@/lib/navigationRedirect';

type NotificationRow = {
  id: number;
  notification_id?: number;
  title?: string;
  body?: string;
  message?: string;
  type?: string;
  data?: Record<string, unknown>;
  booking_id?: number;
  screen?: string;
  is_read?: boolean;
  created_at_ist?: string;
  notification?: {
    title?: string;
    body?: string;
    message?: string;
    type?: string;
    data?: Record<string, unknown>;
    booking_id?: number;
    screen?: string;
  };
};

export default function NotificationsScreen() {
  const [items, setItems] = useState<NotificationRow[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    try {
      const rows = await listNotifications();
      setItems(Array.isArray(rows) ? rows as NotificationRow[] : []);
      setError('');
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Could not load notifications.');
      setItems([]);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  const onRefresh = () => {
    setRefreshing(true);
    load();
  };

  const readAll = async () => {
    await markAllNotificationsRead();
    setItems((prev) => prev.map((item) => ({ ...item, is_read: true })));
  };

  const openItem = async (item: NotificationRow) => {
    if (!item.is_read) {
      await markNotificationRead(item.id).catch(() => undefined);
      setItems((prev) => prev.map((row) => row.id === item.id ? { ...row, is_read: true } : row));
    }

    const nested = item.notification || {};
    await handleNotificationRedirect({
      screen: item.screen || nested.screen,
      booking_id: item.booking_id || nested.booking_id || (item.data?.booking_id as number | undefined) || (nested.data?.booking_id as number | undefined),
      redirect_type: item.data?.redirect_type as string | undefined,
      redirect_value: item.data?.redirect_value as string | number | undefined,
    });
  };

  if (loading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator color="#0877F2" />
        <Text style={styles.muted}>Loading notifications...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.iconBtn}>
          <Ionicons name="chevron-back" size={24} color="#111827" />
        </TouchableOpacity>
        <Text style={styles.title}>Notifications</Text>
        <TouchableOpacity onPress={readAll} disabled={!items.some((item) => !item.is_read)}>
          <Text style={[styles.readAll, !items.some((item) => !item.is_read) && styles.disabled]}>Read all</Text>
        </TouchableOpacity>
      </View>

      <FlatList
        data={items}
        keyExtractor={(item) => String(item.id)}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        contentContainerStyle={items.length ? styles.list : styles.emptyWrap}
        ListEmptyComponent={
          <View style={styles.empty}>
            <Text style={styles.emptyTitle}>{error ? 'Unable to load notifications' : 'No notifications'}</Text>
            <Text style={styles.emptyText}>{error || "You're all caught up."}</Text>
            {error ? (
              <TouchableOpacity style={styles.retry} onPress={() => { setLoading(true); load(); }}>
                <Text style={styles.retryText}>Retry</Text>
              </TouchableOpacity>
            ) : null}
          </View>
        }
        renderItem={({ item }) => {
          const nested = item.notification || {};
          return (
            <TouchableOpacity style={[styles.card, !item.is_read && styles.unread]} onPress={() => openItem(item)}>
              <View style={styles.row}>
                <Text style={styles.cardTitle}>{item.title || nested.title || 'WheelWash Update'}</Text>
                {!item.is_read ? <View style={styles.dot} /> : null}
              </View>
              <Text style={styles.body}>{item.body || item.message || nested.body || nested.message || ''}</Text>
              <Text style={styles.time}>{item.created_at_ist || ''}</Text>
            </TouchableOpacity>
          );
        }}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F6F8FB' },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFFFFF' },
  muted: { marginTop: 10, color: '#64748B' },
  header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingTop: 52, paddingHorizontal: 16, paddingBottom: 14, backgroundColor: '#FFFFFF', borderBottomWidth: 1, borderBottomColor: '#E5E7EB' },
  iconBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  title: { fontSize: 18, fontWeight: '800', color: '#111827' },
  readAll: { color: '#0877F2', fontWeight: '700' },
  disabled: { color: '#94A3B8' },
  list: { padding: 16 },
  emptyWrap: { flexGrow: 1, justifyContent: 'center' },
  empty: { alignItems: 'center', padding: 28 },
  emptyTitle: { fontSize: 18, fontWeight: '800', color: '#111827', textAlign: 'center' },
  emptyText: { marginTop: 8, color: '#64748B', textAlign: 'center' },
  retry: { marginTop: 18, backgroundColor: '#0877F2', borderRadius: 8, paddingHorizontal: 18, paddingVertical: 10 },
  retryText: { color: '#FFFFFF', fontWeight: '800' },
  card: { backgroundColor: '#FFFFFF', borderRadius: 8, padding: 14, marginBottom: 10, borderWidth: 1, borderColor: '#E5E7EB' },
  unread: { borderLeftWidth: 4, borderLeftColor: '#0877F2', backgroundColor: '#F0F7FF' },
  row: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: 10 },
  cardTitle: { flex: 1, fontSize: 15, color: '#111827', fontWeight: '800' },
  dot: { width: 8, height: 8, borderRadius: 4, backgroundColor: '#0877F2' },
  body: { marginTop: 6, color: '#475569', lineHeight: 19 },
  time: { marginTop: 10, color: '#94A3B8', fontSize: 12, fontWeight: '700' },
});
