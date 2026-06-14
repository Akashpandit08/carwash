import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl } from 'react-native';
import { getPartnerEarnings } from '../../api/partnerApi';
import { getWorkerEarnings } from '../../api/workerApi';
import { getDriverEarnings } from '../../api/pickupDriverApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { apiErrorMessage, devLog, extractObject } from '../../utils/apiResponse';
import { getISTDateTimeString } from '../../utils/date';

const money = (value: any) => `₹${Number(value || 0).toFixed(2)}`;

const createEarningsScreen = (fetchFn: () => Promise<any>, roleName: string) => {
  return () => {
    const [data, setData] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState('');

    const loadData = async () => {
      try {
        const res = await fetchFn();
        setData(extractObject(res.data));
        setError('');
      } catch (e) {
        const message = apiErrorMessage(e, 'Could not load earnings.');
        devLog(`[${roleName} earnings error]`, e);
        setError(message);
        setData({});
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

    if (loading) return <LoadingView message={`Loading ${roleName} Earnings...`} />;
    if (error) return <EmptyState title="Unable to load earnings" message={error} actionLabel="Retry" onAction={() => { setLoading(true); loadData(); }} />;

    const transactions = data?.transactions || data?.history || [];

    return (
      <View style={styles.container}>
        <View style={styles.summaryCard}>
          <Text style={styles.summaryTitle}>Total Earnings</Text>
          <Text style={styles.summaryValue}>{money(data?.total_earnings)}</Text>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryMeta}>Pending {money(data?.pending_earnings ?? data?.pending_payout)}</Text>
            <Text style={styles.summaryMeta}>Paid {money(data?.paid_earnings ?? data?.paid_payout)}</Text>
          </View>
        </View>

        <Text style={styles.sectionTitle}>Transaction History</Text>
        <FlatList
          data={transactions}
          keyExtractor={(item, index) => item.id?.toString() || index.toString()}
          renderItem={({ item }) => (
            <View style={styles.card}>
              <View style={styles.cardInfo}>
                <Text style={styles.cardTitle}>{item.description || `Booking #${item.booking_id || item.id}`}</Text>
                <Text style={styles.cardMeta}>{item.date || item.created_at ? getISTDateTimeString(item.date || item.created_at) : ''} • {item.status || item.payout_status || 'pending'}</Text>
              </View>
              <Text style={styles.cardAmount}>+{money(item.amount || item.net_amount)}</Text>
            </View>
          )}
          ListEmptyComponent={<EmptyState title="No Transactions" message="You don't have any recorded earnings yet." />}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
          contentContainerStyle={transactions.length === 0 ? { flex: 1 } : { paddingBottom: 20 }}
        />
      </View>
    );
  };
};

export const PartnerEarningsScreen = createEarningsScreen(getPartnerEarnings, 'Partner');
export const WorkerEarningsScreen = createEarningsScreen(getWorkerEarnings, 'Worker');
export const DriverEarningsScreen = createEarningsScreen(getDriverEarnings, 'Driver');

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  summaryCard: { backgroundColor: '#007BFF', padding: 20, borderRadius: 12, marginBottom: 20, alignItems: 'center' },
  summaryTitle: { fontSize: 16, color: '#E6F4FE', marginBottom: 8 },
  summaryValue: { fontSize: 32, fontWeight: 'bold', color: '#FFF' },
  summaryRow: { flexDirection: 'row', gap: 12, marginTop: 12 },
  summaryMeta: { color: '#E6F4FE', fontWeight: '700' },
  sectionTitle: { fontSize: 18, fontWeight: 'bold', marginBottom: 12, color: '#333' },
  card: { backgroundColor: '#FFF', padding: 16, borderRadius: 8, marginBottom: 12, flexDirection: 'row', justifyContent: 'space-between', elevation: 1 },
  cardInfo: { flex: 1, paddingRight: 10 },
  cardTitle: { fontSize: 16, color: '#333', fontWeight: '700' },
  cardMeta: { marginTop: 4, fontSize: 12, color: '#64748B' },
  cardAmount: { fontSize: 16, fontWeight: 'bold', color: '#28A745' },
});
