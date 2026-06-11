import React, { useEffect, useState } from 'react';
import { ScrollView, View, Text, StyleSheet, RefreshControl } from 'react-native';
import { getDriverDashboard } from '../../api/pickupDriverApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { AppButton } from '../../components/AppButton';

export const DriverDashboardScreen = ({ navigation }: any) => {
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchDashboard = async () => {
    try {
      const res = await getDriverDashboard();
      setData(res.data?.data || res.data || {});
    } catch (e) {
      setData({});
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchDashboard();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchDashboard();
  };

  if (loading) return <LoadingView message="Loading Dashboard..." />;

  return (
    <ScrollView 
      style={styles.container}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
    >
      <Text style={styles.title}>Driver Dashboard</Text>
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Assigned Pickups/Deliveries</Text>
        <Text style={styles.cardValue}>{data?.assigned_jobs || 0}</Text>
      </View>
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Completed Today</Text>
        <Text style={styles.cardValue}>{data?.completed_jobs || 0}</Text>
      </View>
      <AppButton title="View Pickups & Deliveries" onPress={() => navigation.navigate('DriverJobsScreen')} />
      <AppButton title="My Earnings" onPress={() => navigation.navigate('DriverEarningsScreen')} type="secondary" />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  title: { fontSize: 22, fontWeight: 'bold', marginBottom: 16, color: '#333' },
  card: { backgroundColor: '#FFF', padding: 20, borderRadius: 12, marginBottom: 16, elevation: 2 },
  cardTitle: { fontSize: 16, color: '#666', marginBottom: 8 },
  cardValue: { fontSize: 28, fontWeight: 'bold', color: '#007BFF' },
});
