import React, { useEffect, useState } from 'react';
import { View, FlatList, Text, StyleSheet, Alert, TouchableOpacity } from 'react-native';
import { getPartnerWorkers, assignWorkerToJob } from '../../api/partnerApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';

export const PartnerWorkersScreen = ({ route, navigation }: any) => {
  const { bookingId } = route.params || {};
  const [workers, setWorkers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [assigningId, setAssigningId] = useState<any>(null);

  useEffect(() => {
    const fetchWorkers = async () => {
      try {
        const res = await getPartnerWorkers();
        setWorkers(res.data?.data || res.data || []);
      } catch (e) {
        setWorkers([]);
      } finally {
        setLoading(false);
      }
    };
    fetchWorkers();
  }, []);

  const handleAssign = async (workerId: string) => {
    if (!bookingId) return;
    setAssigningId(workerId);
    try {
      await assignWorkerToJob(bookingId, workerId);
      Alert.alert('Success', 'Worker assigned!');
      navigation.goBack();
    } catch (e: any) {
      Alert.alert('Error', e.response?.data?.message || 'Failed to assign worker');
    } finally {
      setAssigningId(null);
    }
  };

  if (loading) return <LoadingView message="Loading Workers..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={workers}
        keyExtractor={(item) => item.id?.toString()}
        renderItem={({ item }) => (
          <View style={styles.card}>
            <Text style={styles.name}>{item.name}</Text>
            <Text style={styles.phone}>{item.phone || 'No phone'}</Text>
            {bookingId && (
              <TouchableOpacity 
                style={styles.assignBtn} 
                onPress={() => handleAssign(item.id)}
                disabled={assigningId !== null}
              >
                <Text style={styles.assignText}>
                  {assigningId === item.id ? 'Assigning...' : 'Assign to Job'}
                </Text>
              </TouchableOpacity>
            )}
          </View>
        )}
        ListEmptyComponent={<EmptyState title="No Workers Found" />}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  card: { backgroundColor: '#FFF', padding: 16, borderRadius: 8, marginBottom: 12, elevation: 1 },
  name: { fontSize: 16, fontWeight: 'bold', color: '#333' },
  phone: { fontSize: 14, color: '#666', marginTop: 4 },
  assignBtn: { marginTop: 12, backgroundColor: '#007BFF', padding: 10, borderRadius: 6, alignItems: 'center' },
  assignText: { color: '#FFF', fontWeight: '600' },
});
