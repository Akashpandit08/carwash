import React, { useEffect, useState } from 'react';
import { View, FlatList, Text, StyleSheet, Alert, TouchableOpacity, Modal, TextInput } from 'react-native';
import apiClient from '../../api/client';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { AppButton } from '../../components/AppButton';
import { Ionicons } from '@expo/vector-icons';

export const PartnerWorkersScreen = ({ route, navigation }: any) => {
  const { bookingId, isSelectionMode } = route.params || {};
  const [workers, setWorkers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [assigningId, setAssigningId] = useState<any>(null);
  
  const [showAddModal, setShowAddModal] = useState(false);
  const [newWorker, setNewWorker] = useState({ name: '', mobile_number: '', password: '' });

  const fetchWorkers = async () => {
    try {
      const res = await apiClient.get('/partner/workers');
      setWorkers(res.data?.data || []);
    } catch (e) {
      setWorkers([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchWorkers();
  }, []);

  const handleAssign = async (workerId: string) => {
    if (!bookingId) return;
    setAssigningId(workerId);
    try {
      await apiClient.post(`/partner/jobs/${bookingId}/assign-worker`, { worker_id: workerId });
      Alert.alert('Success', 'Worker assigned!');
      navigation.goBack();
    } catch (e: any) {
      Alert.alert('Error', e.response?.data?.message || 'Failed to assign worker');
    } finally {
      setAssigningId(null);
    }
  };

  const handleAddWorker = async () => {
    if (!newWorker.name || !newWorker.mobile_number || !newWorker.password) {
      Alert.alert('Error', 'Please fill all fields');
      return;
    }
    setLoading(true);
    try {
      await apiClient.post('/partner/workers', newWorker);
      setShowAddModal(false);
      setNewWorker({ name: '', mobile_number: '', password: '' });
      fetchWorkers();
      Alert.alert('Success', 'Worker added');
    } catch (e: any) {
      Alert.alert('Error', e.response?.data?.message || 'Failed to add worker');
      setLoading(false);
    }
  };

  if (loading && !workers.length) return <LoadingView message="Loading Workers..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={workers}
        keyExtractor={(item) => item.id?.toString()}
        renderItem={({ item }) => (
          <View style={styles.card}>
            <View style={styles.row}>
              <View>
                <Text style={styles.name}>{item.name}</Text>
                <Text style={styles.phone}>{item.mobile_number}</Text>
              </View>
              <View style={styles.badge}>
                <View style={[styles.dot, { backgroundColor: item.current_status === 'online' ? '#28A745' : '#DC3545' }]} />
                <Text style={styles.statusText}>{item.current_status}</Text>
              </View>
            </View>
            
            {isSelectionMode && bookingId && (
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

      {!isSelectionMode && (
        <TouchableOpacity style={styles.fab} onPress={() => setShowAddModal(true)}>
          <Ionicons name="add" size={24} color="#FFF" />
        </TouchableOpacity>
      )}

      <Modal visible={showAddModal} transparent animationType="slide">
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>Add Worker</Text>
            <TextInput style={styles.input} placeholder="Name" value={newWorker.name} onChangeText={(t) => setNewWorker({...newWorker, name: t})} />
            <TextInput style={styles.input} placeholder="Mobile Number" keyboardType="phone-pad" value={newWorker.mobile_number} onChangeText={(t) => setNewWorker({...newWorker, mobile_number: t})} />
            <TextInput style={styles.input} placeholder="Password" secureTextEntry value={newWorker.password} onChangeText={(t) => setNewWorker({...newWorker, password: t})} />
            <AppButton title="Save Worker" onPress={handleAddWorker} />
            <AppButton title="Cancel" type="secondary" onPress={() => setShowAddModal(false)} style={{ marginTop: 8 }} />
          </View>
        </View>
      </Modal>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  card: { backgroundColor: '#FFF', padding: 16, borderRadius: 8, marginBottom: 12, elevation: 1 },
  row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  name: { fontSize: 16, fontWeight: 'bold', color: '#333' },
  phone: { fontSize: 14, color: '#666', marginTop: 4 },
  badge: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F0F0F0', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 12 },
  dot: { width: 8, height: 8, borderRadius: 4, marginRight: 6 },
  statusText: { fontSize: 12, fontWeight: 'bold', color: '#555', textTransform: 'capitalize' },
  assignBtn: { marginTop: 12, backgroundColor: '#007BFF', padding: 10, borderRadius: 6, alignItems: 'center' },
  assignText: { color: '#FFF', fontWeight: '600' },
  fab: { position: 'absolute', bottom: 20, right: 20, backgroundColor: '#007BFF', width: 56, height: 56, borderRadius: 28, justifyContent: 'center', alignItems: 'center', elevation: 4 },
  modalContainer: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', padding: 20 },
  modalContent: { backgroundColor: '#FFF', padding: 20, borderRadius: 12 },
  modalTitle: { fontSize: 20, fontWeight: 'bold', marginBottom: 16 },
  input: { borderWidth: 1, borderColor: '#DDD', padding: 12, borderRadius: 8, marginBottom: 12 },
});
