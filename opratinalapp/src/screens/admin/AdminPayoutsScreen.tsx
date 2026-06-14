import React, { useEffect, useState } from 'react';
import { SafeScreen } from '../../components/SafeScreen';
import { View, Text, StyleSheet, FlatList, RefreshControl, TouchableOpacity, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { getPayouts, approvePayout, markPayoutPaid } from '../../api/adminApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';

export const AdminPayoutsScreen = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await getPayouts();
      const payload = res.data?.data || res.data || [];
      setData(Array.isArray(payload) ? payload : payload.data || []);
    } catch (e) {
      console.log('Failed to fetch payouts', e);
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
    Alert.alert(title, `Are you sure you want to ${title.toLowerCase()} this payout request?`, [
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

  if (loading) return <LoadingView message="Loading Payouts..." />;

  return (
    <SafeScreen style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item) => item.id?.toString()}
        renderItem={({ item }) => (
          <View style={styles.card}>
            <View style={styles.header}>
              <Text style={styles.name}>{item.partner?.business_name || 'Partner'}</Text>
              <Text style={[styles.badge, 
                item.status === 'paid' ? styles.badgePaid : 
                item.status === 'approved' ? styles.badgeApproved : styles.badgePending
              ]}>
                {item.status}
              </Text>
            </View>
            <Text style={styles.amount}>₹{item.amount}</Text>
            <Text style={styles.detail}>Date: {new Date(item.created_at).toLocaleDateString()}</Text>
            
            <View style={styles.actionsRow}>
              {item.status === 'pending' && (
                <TouchableOpacity style={styles.actionBtn} onPress={() => handleAction(approvePayout, item.id, 'Approve')}>
                  <Ionicons name="checkmark-circle-outline" size={20} color="#28A745" />
                  <Text style={[styles.actionText, { color: '#28A745' }]}>Approve</Text>
                </TouchableOpacity>
              )}
              {item.status === 'approved' && (
                <TouchableOpacity style={styles.actionBtn} onPress={() => handleAction(markPayoutPaid, item.id, 'Mark Paid')}>
                  <Ionicons name="cash-outline" size={20} color="#007BFF" />
                  <Text style={[styles.actionText, { color: '#007BFF' }]}>Mark Paid</Text>
                </TouchableOpacity>
              )}
            </View>
          </View>
        )}
        ListEmptyComponent={<EmptyState title="No Payout Requests Found" />}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
      />
    </SafeScreen>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  card: { backgroundColor: '#FFF', borderRadius: 12, padding: 16, marginBottom: 12, elevation: 1 },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
  name: { fontSize: 16, fontWeight: 'bold', color: '#333' },
  badge: { paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6, fontSize: 12, fontWeight: 'bold', textTransform: 'capitalize' },
  badgePaid: { backgroundColor: '#E3FCEF', color: '#006644' },
  badgeApproved: { backgroundColor: '#EAF4FF', color: '#007BFF' },
  badgePending: { backgroundColor: '#FFF0B3', color: '#FF991F' },
  amount: { fontSize: 22, fontWeight: 'bold', color: '#28A745', marginBottom: 4 },
  detail: { fontSize: 14, color: '#666' },
  actionsRow: { flexDirection: 'row', gap: 12, marginTop: 12, borderTopWidth: 1, borderTopColor: '#EEE', paddingTop: 12 },
  actionBtn: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  actionText: { fontWeight: 'bold' }
});
