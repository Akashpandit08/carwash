import React, { useEffect, useState } from 'react';
import { ScrollView, View, Text, StyleSheet, RefreshControl, TouchableOpacity } from 'react-native';
import { LoadingView } from '../../components/LoadingView';
import { AppButton } from '../../components/AppButton';
import apiClient from '../../api/client';
import { Ionicons } from '@expo/vector-icons';

export const PartnerDashboardScreen = ({ navigation }: any) => {
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchDashboard = async () => {
    try {
      const res = await apiClient.get('/partner/dashboard');
      setData(res.data?.data || {});
    } catch (e) {
      console.error('Failed to fetch dashboard', e);
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

  const hasAlerts = 
    (data?.pending_worker_assignments > 0) || 
    (data?.pickup_arriving_soon > 0) || 
    (data?.pending_acceptance > 0);

  return (
    <ScrollView 
      style={styles.container}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
    >
      <View style={styles.header}>
        <Text style={styles.title}>Washing Center Dashboard</Text>
        <TouchableOpacity onPress={() => navigation.navigate('PartnerProfileScreen')}>
          <Ionicons name="person-circle" size={32} color="#007BFF" />
        </TouchableOpacity>
      </View>

      {hasAlerts && (
        <View style={styles.alertsContainer}>
          <Text style={styles.sectionTitle}>Action Needed</Text>
          {data?.pending_worker_assignments > 0 && (
            <View style={styles.alertCard}>
              <Ionicons name="warning" size={24} color="#FF9800" />
              <Text style={styles.alertText}>{data.pending_worker_assignments} bookings need worker assigned</Text>
            </View>
          )}
          {data?.pickup_arriving_soon > 0 && (
            <View style={styles.alertCard}>
              <Ionicons name="car" size={24} color="#2196F3" />
              <Text style={styles.alertText}>{data.pickup_arriving_soon} pickup cars arriving soon</Text>
            </View>
          )}
          {data?.pending_acceptance > 0 && (
            <View style={styles.alertCard}>
              <Ionicons name="checkmark-circle" size={24} color="#F44336" />
              <Text style={styles.alertText}>{data.pending_acceptance} bookings need acceptance</Text>
            </View>
          )}
        </View>
      )}

      <Text style={styles.sectionTitle}>Today's Overview</Text>
      
      <View style={styles.statsGrid}>
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Earnings</Text>
          <Text style={styles.cardValue}>₹{data?.today_earnings || '0.00'}</Text>
        </View>
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Total Bookings</Text>
          <Text style={[styles.cardValue, { color: '#007BFF' }]}>{data?.today_bookings || 0}</Text>
        </View>
      </View>

      <View style={styles.statsGrid}>
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Workers</Text>
          <View style={styles.statusRow}>
            <Text style={styles.statusOnline}>{data?.active_workers || 0} Online</Text>
            <Text style={styles.statusOffline}>{data?.offline_workers || 0} Offline</Text>
          </View>
        </View>
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Drivers</Text>
          <View style={styles.statusRow}>
            <Text style={styles.statusOnline}>{data?.active_drivers || 0} Online</Text>
            <Text style={styles.statusOffline}>{data?.offline_drivers || 0} Offline</Text>
          </View>
        </View>
      </View>

      <Text style={styles.sectionTitle}>Quick Links</Text>
      <View style={styles.quickLinks}>
        <AppButton title="My Jobs" onPress={() => navigation.navigate('PartnerJobsScreen')} />
        <AppButton title="My Earnings" onPress={() => navigation.navigate('PartnerEarningsScreen')} type="secondary" />
        <AppButton title="Manage Workers" onPress={() => navigation.navigate('PartnerWorkersScreen')} type="secondary" />
        <AppButton title="Manage Drivers" onPress={() => navigation.navigate('PartnerDriversScreen')} type="secondary" />
        <AppButton title="Notifications" onPress={() => navigation.navigate('NotificationsScreen')} type="secondary" />
      </View>
      <View style={{ height: 40 }} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 },
  title: { fontSize: 24, fontWeight: 'bold', color: '#333' },
  sectionTitle: { fontSize: 18, fontWeight: 'bold', color: '#444', marginBottom: 12, marginTop: 8 },
  alertsContainer: { marginBottom: 16 },
  alertCard: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#FFF', padding: 16, borderRadius: 12, marginBottom: 8, borderWidth: 1, borderColor: '#FFE0B2' },
  alertText: { fontSize: 16, fontWeight: '600', color: '#555', marginLeft: 12 },
  statsGrid: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 12 },
  card: { backgroundColor: '#FFF', padding: 16, borderRadius: 12, elevation: 2, flex: 0.48 },
  cardTitle: { fontSize: 14, color: '#666', marginBottom: 8 },
  cardValue: { fontSize: 24, fontWeight: 'bold', color: '#28A745' },
  statusRow: { flexDirection: 'row', justifyContent: 'space-between', marginTop: 4 },
  statusOnline: { fontSize: 14, color: '#28A745', fontWeight: 'bold' },
  statusOffline: { fontSize: 14, color: '#999' },
  quickLinks: { gap: 12 },
});
