import React, { useEffect, useState } from 'react';
import { View, FlatList, StyleSheet, RefreshControl } from 'react-native';
import { getWorkerJobs } from '../../api/workerApi';
import { BookingCard } from '../../components/BookingCard';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';

export const WorkerJobsScreen = ({ navigation }: any) => {
  const [jobs, setJobs] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchJobs = async () => {
    try {
      const res = await getWorkerJobs();
      setJobs(res.data?.data || res.data || []);
    } catch (e) {
      setJobs([]);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    const unsubscribe = navigation.addListener('focus', () => fetchJobs());
    return unsubscribe;
  }, [navigation]);

  const onRefresh = () => {
    setRefreshing(true);
    fetchJobs();
  };

  if (loading) return <LoadingView message="Loading Jobs..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={jobs}
        keyExtractor={(item) => item.id?.toString()}
        renderItem={({ item }) => (
          <BookingCard 
            booking={item} 
            onPress={() => navigation.navigate('WorkerJobDetailScreen', { bookingId: item.id })} 
          />
        )}
        ListEmptyComponent={<EmptyState title="No Assigned Jobs" message="You have no jobs assigned at the moment." />}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        contentContainerStyle={jobs.length === 0 ? { flex: 1 } : { paddingBottom: 20 }}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
});
