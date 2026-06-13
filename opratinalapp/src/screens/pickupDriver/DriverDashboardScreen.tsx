import React, { useEffect, useState } from 'react';
import { RefreshControl, ScrollView, StyleSheet, Switch, Text, TouchableOpacity, View } from 'react-native';
import { getDriverDashboard, updateDriverOnlineStatus } from '../../api/pickupDriverApi';
import { ensureLocationPermission, startLiveTracking } from '../../services/locationTracking';
import { LoadingView } from '../../components/LoadingView';
import { AppButton } from '../../components/AppButton';

export const DriverDashboardScreen = ({ navigation }: any) => {
  const [data, setData] = useState<any>({});
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [online, setOnline] = useState(false);

  const fetchDashboard = async () => {
    try {
      const res = await getDriverDashboard();
      const payload = res.data?.data || res.data || {};
      setData(payload);
      setOnline(Boolean(payload.is_online));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => { fetchDashboard(); }, []);
  useEffect(() => online ? startLiveTracking('pickup_driver', () => online) : undefined, [online]);

  const toggleOnline = async (value: boolean) => {
    if (value) {
      const granted = await ensureLocationPermission();
      if (!granted) return;
    }
    setOnline(value);
    await updateDriverOnlineStatus(value);
  };

  if (loading) return <LoadingView message="Loading dashboard..." />;

  return (
    <ScrollView style={styles.container} refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); fetchDashboard(); }} />}>
      <View style={styles.header}>
        <Text style={styles.title}>Pickup Driver Dashboard</Text>
        <TouchableOpacity onPress={() => navigation.navigate('PickupDriverNotificationsScreen')}>
          <Text style={styles.link}>Notifications</Text>
        </TouchableOpacity>
      </View>
      <View style={styles.onlineCard}>
        <Text style={styles.cardTitle}>{online ? 'Online' : 'Offline'}</Text>
        <Switch value={online} onValueChange={toggleOnline} />
      </View>
      <View style={styles.grid}>
        <Metric title="Upcoming Pickups" value={data.upcoming_pickups ?? 0} />
        <Metric title="Upcoming Deliveries" value={data.upcoming_deliveries ?? 0} />
        <Metric title="Completed Trips" value={data.completed_trips ?? data.completed_jobs ?? 0} />
        <Metric title="Today Earnings" value={`Rs ${data.today_earnings ?? data.total_earnings ?? 0}`} />
      </View>
      {data.active_job && (
        <TouchableOpacity style={styles.activeCard} onPress={() => navigation.navigate('PickupDriverJobDetailScreen', { bookingId: data.active_job.id })}>
          <Text style={styles.cardTitle}>Active Trip #{data.active_job.booking_number || data.active_job.id}</Text>
          <Text style={styles.muted}>{data.active_job.address || data.active_job.pickup_address}</Text>
        </TouchableOpacity>
      )}
      <AppButton title="View Jobs" onPress={() => navigation.navigate('PickupDriverJobsScreen')} />
      <AppButton title="My Earnings" onPress={() => navigation.navigate('PickupDriverEarningsScreen')} type="secondary" />
      <AppButton title="Profile" onPress={() => navigation.navigate('PickupDriverProfileScreen')} type="secondary" />
    </ScrollView>
  );
};

const Metric = ({ title, value }: any) => (
  <View style={styles.metric}><Text style={styles.metricLabel}>{title}</Text><Text style={styles.metricValue}>{value}</Text></View>
);

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
  title: { fontSize: 22, fontWeight: '800', color: '#111827', flex: 1 },
  link: { color: '#2563EB', fontWeight: '700' },
  onlineCard: { backgroundColor: '#FFFFFF', padding: 16, borderRadius: 8, marginBottom: 14, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10, marginBottom: 14 },
  metric: { width: '48%', backgroundColor: '#FFFFFF', padding: 16, borderRadius: 8, borderWidth: 1, borderColor: '#E5E7EB' },
  metricLabel: { fontSize: 13, color: '#64748B', marginBottom: 8 },
  metricValue: { fontSize: 22, color: '#111827', fontWeight: '800' },
  activeCard: { backgroundColor: '#ECFEFF', padding: 16, borderRadius: 8, marginBottom: 14, borderWidth: 1, borderColor: '#67E8F9' },
  cardTitle: { fontSize: 16, fontWeight: '800', color: '#111827' },
  muted: { color: '#64748B', marginTop: 4 },
});
