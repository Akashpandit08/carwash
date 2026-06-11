import React, { useEffect, useState } from 'react';
import { ScrollView, View, Text, StyleSheet, RefreshControl } from 'react-native';
import { getPartnerDashboard } from '../../api/partnerApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { AppButton } from '../../components/AppButton';

export const PartnerDashboardScreen = ({ navigation }: any) => {
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchDashboard = async () => {
    try {
      const res = await getPartnerDashboard();
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
      <Text style={styles.title}>Partner Overview</Text>
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Today's Jobs</Text>
        <Text style={styles.cardValue}>{data?.todays_jobs || 0}</Text>
      </View>
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Active Workers</Text>
        <Text style={styles.cardValue}>{data?.active_workers || 0}</Text>
      </View>
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Today's Earnings</Text>
        <Text style={styles.cardValue}>${data?.todays_earnings || '0.00'}</Text>
      </View>
      <AppButton title="View Jobs" onPress={() => navigation.navigate('PartnerJobsScreen')} />
      <AppButton title="My Earnings" onPress={() => navigation.navigate('PartnerEarningsScreen')} type="secondary" />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  title: { fontSize: 22, fontWeight: 'bold', marginBottom: 16, color: '#333' },
  card: { backgroundColor: '#FFF', padding: 20, borderRadius: 12, marginBottom: 16, elevation: 2 },
  cardTitle: { fontSize: 16, color: '#666', marginBottom: 8 },
  cardValue: { fontSize: 28, fontWeight: 'bold', color: '#28A745' },
});
