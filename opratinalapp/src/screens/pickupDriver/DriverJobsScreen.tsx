import React, { useEffect, useState } from 'react';
import { FlatList, RefreshControl, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { getDriverJobs } from '../../api/pickupDriverApi';
import { getPickupDriverAction } from '../../utils/statusFlow';
import { BookingCard } from '../../components/BookingCard';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { apiErrorMessage, devLog, extractCollection } from '../../utils/apiResponse';

const tabs = [
  { key: 'pickup', label: 'To Pick Up' },
  { key: 'delivery', label: 'To Deliver' },
  { key: 'completed', label: 'Completed' },
];

export const DriverJobsScreen = ({ navigation }: any) => {
  const [activeTab, setActiveTab] = useState('pickup');
  const [jobs, setJobs] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');

  const fetchJobs = async () => {
    try {
      const res = await getDriverJobs(activeTab);
      setJobs(extractCollection(res.data));
      setError('');
    } catch (e) {
      const message = apiErrorMessage(e, 'Could not load trips.');
      devLog('[Pickup driver jobs error]', e);
      setError(message);
      setJobs([]);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchJobs();
    const unsubscribe = navigation.addListener('focus', fetchJobs);
    return unsubscribe;
  }, [navigation, activeTab]);

  if (loading) return <LoadingView message="Loading trips..." />;
  if (error) return <EmptyState title="Unable to load jobs" message={error} actionLabel="Retry" onAction={() => { setLoading(true); fetchJobs(); }} />;

  return (
    <View style={styles.container}>
      <View style={styles.tabs}>
        {tabs.map((tab) => (
          <TouchableOpacity key={tab.key} style={[styles.tab, activeTab === tab.key && styles.activeTab]} onPress={() => { setActiveTab(tab.key); setLoading(true); }}>
            <Text style={[styles.tabText, activeTab === tab.key && styles.activeTabText]}>{tab.label}</Text>
          </TouchableOpacity>
        ))}
      </View>
      <FlatList
        data={jobs}
        keyExtractor={(item) => String(item.id)}
        renderItem={({ item }) => <BookingCard booking={{ ...item, action_hint: getPickupDriverAction(item)?.label }} onPress={() => navigation.navigate('PickupDriverJobDetailScreen', { bookingId: item.id })} />}
        ListEmptyComponent={<EmptyState title="No jobs assigned yet" message="No trips found for this tab." />}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); fetchJobs(); }} />}
        contentContainerStyle={jobs.length === 0 ? { flex: 1 } : { paddingBottom: 20 }}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  tabs: { flexDirection: 'row', gap: 8, marginBottom: 14 },
  tab: { flex: 1, paddingVertical: 10, borderRadius: 8, backgroundColor: '#FFFFFF', alignItems: 'center', borderWidth: 1, borderColor: '#E5E7EB' },
  activeTab: { backgroundColor: '#111827', borderColor: '#111827' },
  tabText: { fontWeight: '700', color: '#475569', fontSize: 12 },
  activeTabText: { color: '#FFFFFF' },
});
