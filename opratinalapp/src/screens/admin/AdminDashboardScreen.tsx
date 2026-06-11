import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, RefreshControl } from 'react-native';
import { getDashboard } from '../../api/adminApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { AppButton } from '../../components/AppButton';

export const AdminDashboardScreen = ({ navigation }: any) => {
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchDashboard = async () => {
    try {
      const res = await getDashboard();
      setData(res.data?.data || res.data || {});
    } catch (e) {
      console.log('Admin Dashboard error', e);
      setData({}); // Defensive empty
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

  if (!data || Object.keys(data).length === 0) {
    return (
      <ScrollView refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}>
        <EmptyState title="No Data" message="Unable to load dashboard data." />
      </ScrollView>
    );
  }

  return (
    <ScrollView 
      style={styles.container}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
    >
      <Text style={styles.title}>Overview</Text>
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Total Bookings</Text>
        <Text style={styles.cardValue}>{data?.total_bookings || 0}</Text>
      </View>
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Active Partners</Text>
        <Text style={styles.cardValue}>{data?.active_partners || 0}</Text>
      </View>
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Total Revenue</Text>
        <Text style={styles.cardValue}>${data?.total_revenue || '0.00'}</Text>
      </View>
      <Text style={styles.sectionTitle}>Management</Text>
      <AppButton title="View Bookings" onPress={() => navigation.navigate('AdminBookingsScreen')} />
      <AppButton title="Manage Partners" onPress={() => navigation.navigate('AdminPartnersScreen')} type="secondary" />
      <AppButton title="Manage Workers" onPress={() => navigation.navigate('AdminWorkersScreen')} type="secondary" />
      <AppButton title="Manage Drivers" onPress={() => navigation.navigate('AdminDriversScreen')} type="secondary" />
      <AppButton title="Manage Services" onPress={() => navigation.navigate('AdminServicesScreen')} type="secondary" />
      <AppButton title="Manage Slots" onPress={() => navigation.navigate('AdminSlotsScreen')} type="secondary" />
      <AppButton title="Manage Coupons" onPress={() => navigation.navigate('AdminCouponsScreen')} type="secondary" />
      <AppButton title="View Reports" onPress={() => navigation.navigate('AdminReportsScreen')} type="secondary" />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  title: { fontSize: 22, fontWeight: 'bold', marginBottom: 16, color: '#333' },
  sectionTitle: { fontSize: 18, fontWeight: 'bold', marginTop: 16, marginBottom: 8, color: '#333' },
  card: { backgroundColor: '#FFF', padding: 20, borderRadius: 12, marginBottom: 16, elevation: 2, shadowColor: '#000', shadowOpacity: 0.1, shadowRadius: 4 },
  cardTitle: { fontSize: 16, color: '#666', marginBottom: 8 },
  cardValue: { fontSize: 28, fontWeight: 'bold', color: '#007BFF' },
});
