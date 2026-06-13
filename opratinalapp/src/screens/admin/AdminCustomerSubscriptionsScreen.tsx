import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl, TouchableOpacity, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { getCustomerSubscriptions, activateCustomerSubscription, cancelCustomerSubscription, markCustomerSubscriptionPaid } from '../../api/adminApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';

export const AdminCustomerSubscriptionsScreen = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await getCustomerSubscriptions();
      const payload = res.data?.data || res.data || [];
      setData(Array.isArray(payload) ? payload : payload.data || []);
    } catch (e) {
      console.log('Failed to fetch customer subscriptions', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => { loadData(); }, []);

  const onRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const handleAction = async (actionFn: any, id: any, title: string) => {
    Alert.alert(title, `Are you sure you want to ${title.toLowerCase()} this subscription?`, [
      { text: 'Cancel', style: 'cancel' },
      { 
        text: 'Confirm', 
        onPress: async () => {
          try {
            await actionFn(id);
            loadData();
          } catch (e: any) {
            Alert.alert('Error', e.response?.data?.message || 'Action failed');
          }
        }
      }
    ]);
  };

  if (loading) return <LoadingView message="Loading Subscriptions..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item) => item.id?.toString()}
        renderItem={({ item }) => (
          <View style={styles.card}>
            <View style={styles.header}>
              <Text style={styles.name}>{item.user?.name || 'Customer'}</Text>
              <Text style={[styles.badge, item.status === 'active' ? styles.badgeActive : styles.badgeInactive]}>
                {item.status}
              </Text>
            </View>
            <Text style={styles.detail}>Plan: {item.subscription_plan?.name}</Text>
            <Text style={styles.detail}>Remaining: {item.remaining_washes}/{item.total_washes}</Text>
            <Text style={styles.detail}>Payment: {item.payment_status}</Text>
            
            <View style={styles.actionsRow}>
              {item.status === 'pending' && (
                <TouchableOpacity style={styles.actionBtn} onPress={() => handleAction(activateCustomerSubscription, item.id, 'Activate')}>
                  <Ionicons name="checkmark-circle-outline" size={20} color="#28A745" />
                  <Text style={[styles.actionText, { color: '#28A745' }]}>Activate</Text>
                </TouchableOpacity>
              )}
              {item.payment_status === 'pending' && (
                <TouchableOpacity style={styles.actionBtn} onPress={() => handleAction(markCustomerSubscriptionPaid, item.id, 'Mark Paid')}>
                  <Ionicons name="cash-outline" size={20} color="#007BFF" />
                  <Text style={[styles.actionText, { color: '#007BFF' }]}>Mark Paid</Text>
                </TouchableOpacity>
              )}
              {item.status !== 'cancelled' && (
                <TouchableOpacity style={styles.actionBtn} onPress={() => handleAction(cancelCustomerSubscription, item.id, 'Cancel')}>
                  <Ionicons name="close-circle-outline" size={20} color="#FF3B30" />
                  <Text style={[styles.actionText, { color: '#FF3B30' }]}>Cancel</Text>
                </TouchableOpacity>
              )}
            </View>
          </View>
        )}
        ListEmptyComponent={<EmptyState title="No Subscriptions Found" />}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  card: { backgroundColor: '#FFF', borderRadius: 12, padding: 16, marginBottom: 12, elevation: 1 },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
  name: { fontSize: 16, fontWeight: 'bold', color: '#333' },
  badge: { paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6, fontSize: 12, fontWeight: 'bold', textTransform: 'capitalize' },
  badgeActive: { backgroundColor: '#E3FCEF', color: '#006644' },
  badgeInactive: { backgroundColor: '#FFEBE6', color: '#BF2600' },
  detail: { fontSize: 14, color: '#666', marginTop: 4 },
  actionsRow: { flexDirection: 'row', gap: 12, marginTop: 12, borderTopWidth: 1, borderTopColor: '#EEE', paddingTop: 12 },
  actionBtn: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  actionText: { fontWeight: 'bold' }
});
