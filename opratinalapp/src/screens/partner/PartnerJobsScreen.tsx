import React, { useEffect, useState } from 'react';
import { View, FlatList, StyleSheet, RefreshControl, Text, TouchableOpacity } from 'react-native';
import apiClient from '../../api/client';
import { getPartnerJobs } from '../../api/partnerApi';
import { BookingCard } from '../../components/BookingCard';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { apiErrorMessage, devLog, extractCollection } from '../../utils/apiResponse';

const TABS = [
  { id: 'new', label: 'New / Action Req' },
  { id: 'in_progress', label: 'In Progress' },
  { id: 'completed', label: 'Completed' },
  { id: 'all', label: 'All Jobs' },
];

export const PartnerJobsScreen = ({ navigation }: any) => {
  const [jobs, setJobs] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [activeTab, setActiveTab] = useState('new');
  const [error, setError] = useState('');

  const fetchJobs = async (tab = activeTab) => {
    try {
      const res = await getPartnerJobs(tab);
      setJobs(extractCollection(res.data));
      setError('');
    } catch (e) {
      const message = apiErrorMessage(e, 'Could not load jobs.');
      devLog('[Partner jobs error]', e);
      setError(message);
      setJobs([]);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    setLoading(true);
    fetchJobs(activeTab);
  }, [activeTab]);

  const onRefresh = () => {
    setRefreshing(true);
    fetchJobs(activeTab);
  };

  return (
    <View style={styles.container}>
      <View style={styles.tabContainer}>
        <FlatList
          horizontal
          showsHorizontalScrollIndicator={false}
          data={TABS}
          keyExtractor={(item) => item.id}
          renderItem={({ item }) => (
            <TouchableOpacity
              style={[styles.tab, activeTab === item.id && styles.activeTab]}
              onPress={() => setActiveTab(item.id)}
            >
              <Text style={[styles.tabText, activeTab === item.id && styles.activeTabText]}>
                {item.label}
              </Text>
            </TouchableOpacity>
          )}
        />
      </View>

      {loading ? (
        <LoadingView message="Loading Jobs..." />
      ) : error ? (
        <EmptyState title="Unable to load jobs" message={error} actionLabel="Retry" onAction={() => { setLoading(true); fetchJobs(activeTab); }} />
      ) : (
        <FlatList
          data={jobs}
          keyExtractor={(item) => item.id?.toString()}
          renderItem={({ item }) => (
            <BookingCard 
              booking={item} 
              onPress={() => navigation.navigate('PartnerJobDetailScreen', { bookingId: item.id })} 
            />
          )}
          ListEmptyComponent={<EmptyState title="No jobs assigned yet" message="You don't have any jobs in this category." />}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
          contentContainerStyle={jobs.length === 0 ? { flex: 1 } : { paddingBottom: 20, paddingTop: 10 }}
        />
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA' },
  tabContainer: { backgroundColor: '#FFF', paddingVertical: 12, paddingHorizontal: 8, elevation: 2 },
  tab: { paddingHorizontal: 16, paddingVertical: 8, borderRadius: 20, marginHorizontal: 4, backgroundColor: '#F0F0F0' },
  activeTab: { backgroundColor: '#007BFF' },
  tabText: { color: '#666', fontWeight: 'bold' },
  activeTabText: { color: '#FFF' },
});
