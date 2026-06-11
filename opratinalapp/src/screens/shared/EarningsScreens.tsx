import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl } from 'react-native';
import { getPartnerEarnings } from '../../api/partnerApi';
import { getWorkerEarnings } from '../../api/workerApi';
import { getDriverEarnings } from '../../api/pickupDriverApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';

const createEarningsScreen = (fetchFn: () => Promise<any>, roleName: string) => {
  return () => {
    const [data, setData] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const loadData = async () => {
      try {
        const res = await fetchFn();
        setData(res.data?.data || res.data || {});
      } catch (e) {
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

    const transactions = data?.transactions || data?.history || [];

    return (
      <View style={styles.container}>
        <View style={styles.summaryCard}>
          <Text style={styles.summaryTitle}>Total Earnings</Text>
          <Text style={styles.summaryValue}>${data?.total_earnings || '0.00'}</Text>
        </View>

        <Text style={styles.sectionTitle}>Transaction History</Text>
        <FlatList
          data={transactions}
          keyExtractor={(item, index) => item.id?.toString() || index.toString()}
          renderItem={({ item }) => (
            <View style={styles.card}>
              <Text style={styles.cardTitle}>{item.description || `Booking #${item.booking_id || item.id}`}</Text>
              <Text style={styles.cardAmount}>+${item.amount || '0.00'}</Text>
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
  sectionTitle: { fontSize: 18, fontWeight: 'bold', marginBottom: 12, color: '#333' },
  card: { backgroundColor: '#FFF', padding: 16, borderRadius: 8, marginBottom: 12, flexDirection: 'row', justifyContent: 'space-between', elevation: 1 },
  cardTitle: { fontSize: 16, color: '#333' },
  cardAmount: { fontSize: 16, fontWeight: 'bold', color: '#28A745' },
});
