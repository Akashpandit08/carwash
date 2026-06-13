import React, { useEffect, useState } from 'react';
import { FlatList, RefreshControl, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { getWorkerJobs } from '../../api/workerApi';
import { getWorkerAction } from '../../utils/statusFlow';
import { BookingCard } from '../../components/BookingCard';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';

const tabs = ['today', 'upcoming', 'completed'];

export const WorkerJobsScreen = ({ navigation }: any) => {
  const [activeTab, setActiveTab] = useState('today');
  const [jobs, setJobs] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchJobs = async () => {
    try {
      const res = await getWorkerJobs(activeTab);
      const items = res.data?.data?.data || res.data?.data || res.data || [];
      setJobs(Array.isArray(items) ? items : []);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    const unsubscribe = navigation.addListener('focus', fetchJobs);
    return unsubscribe;
  }, [navigation, activeTab]);

  if (loading) return <LoadingView message="Loading jobs..." />;

  return (
    <View style={styles.container}>
      <View style={styles.tabs}>
        {tabs.map((tab) => (
          <TouchableOpacity key={tab} style={[styles.tab, activeTab === tab && styles.activeTab]} onPress={() => { setActiveTab(tab); setLoading(true); }}>
            <Text style={[styles.tabText, activeTab === tab && styles.activeTabText]}>{tab[0].toUpperCase() + tab.slice(1)}</Text>
          </TouchableOpacity>
        ))}
      </View>
      <FlatList
        data={jobs}
        keyExtractor={(item) => String(item.id)}
        renderItem={({ item }) => <BookingCard booking={{ ...item, action_hint: getWorkerAction(item)?.label }} onPress={() => navigation.navigate('WorkerJobDetailScreen', { bookingId: item.id })} />}
        ListEmptyComponent={<EmptyState title="No Jobs" message="No jobs found for this tab." />}
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
  tabText: { fontWeight: '700', color: '#475569' },
  activeTabText: { color: '#FFFFFF' },
});
