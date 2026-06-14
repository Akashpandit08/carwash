import React, { useEffect, useState } from 'react';
import { SafeScreen } from '../../components/SafeScreen';
import { View, Text, StyleSheet, ScrollView, RefreshControl } from 'react-native';
import { getReports } from '../../api/adminApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';

export const AdminReportsScreen = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await getReports();
      const payload = res.data?.data || res.data || [];
      setData(Array.isArray(payload) ? payload : payload.data || []);
    } catch (e) {
      console.log('Failed to fetch reports', e);
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

  if (loading) return <LoadingView message="Loading Reports..." />;

  return (
    <ScrollView 
      style={styles.container}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
    >
      {data.length === 0 ? (
        <EmptyState title="No Reports Found" message="Check back later for analytics." />
      ) : (
        data.map((item, i) => (
          <View key={i} style={styles.card}>
            <Text style={styles.name}>{item.title || `Report #${item.id || i}`}</Text>
            <Text style={styles.detail}>{item.summary || JSON.stringify(item)}</Text>
          </View>
        ))
      )}
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  card: { backgroundColor: '#FFF', borderRadius: 12, padding: 16, marginBottom: 12, elevation: 1 },
  name: { fontSize: 18, fontWeight: 'bold', color: '#333', marginBottom: 8 },
  detail: { fontSize: 14, color: '#666' }
});
