import React, { useEffect, useState } from 'react';
import { View, FlatList, Text, StyleSheet, Alert, TouchableOpacity, Modal, TextInput } from 'react-native';
import apiClient from '../../api/client';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { AppButton } from '../../components/AppButton';
import { Ionicons } from '@expo/vector-icons';

export const PartnerDriversScreen = ({ route, navigation }: any) => {
  const { bookingId, isSelectionMode } = route.params || {};
  const [drivers, setDrivers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [assigningId, setAssigningId] = useState<any>(null);
  
  const [showAddModal, setShowAddModal] = useState(false);
  const [newDriver, setNewDriver] = useState({ name: '', mobile_number: '', password: '', vehicle_type: '' });

  const fetchDrivers = async () => {
    try {
      const res = await apiClient.get('/partner/drivers');
      setDrivers(res.data?.data || []);
    } catch (e) {
      setDrivers([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchDrivers();
  }, []);

  const handleAssign = async (driverId: string) => {
    if (!bookingId) return;
    setAssigningId(driverId);
    try {
      await apiClient.post(`/partner/jobs/${bookingId}/assign-driver`, { driver_id: driverId });
      Alert.alert('Success', 'Driver assigned!');
      navigation.goBack();
    } catch (e: any) {
      Alert.alert('Error', e.response?.data?.message || 'Failed to assign driver');
    } finally {
      setAssigningId(null);
    }
  };

  const handleAddDriver = async () => {
    if (!newDriver.name || !newDriver.mobile_number || !newDriver.password) {
      Alert.alert('Error', 'Please fill all required fields');
      return;
    }
    setLoading(true);
    try {
      await apiClient.post('/partner/drivers', newDriver);
      setShowAddModal(false);
      setNewDriver({ name: '', mobile_number: '', password: '', vehicle_type: '' });
      fetchDrivers();
      Alert.alert('Success', 'Driver added');
    } catch (e: any) {
      Alert.alert('Error', e.response?.data?.message || 'Failed to add driver');
      setLoading(false);
    }
  };

  if (loading && !drivers.length) return <LoadingView message="Loading Drivers..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={drivers}
        keyExtractor={(item) => item.id?.toString()}
        renderItem={({ item }) => (
          <View style={styles.card}>
            <View style={styles.row}>
              <View>
                <Text style={styles.name}>{item.name}</Text>
                <Text style={styles.phone}>{item.mobile_number}</Text>
                <Text style={styles.vehicle}>{item.vehicle_type || 'No Vehicle Info'}</Text>
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
        ListEmptyComponent={<EmptyState title="No Drivers Found" />}
      />

      {!isSelectionMode && (
        <TouchableOpacity style={styles.fab} onPress={() => setShowAddModal(true)}>
          <Ionicons name="add" size={24} color="#FFF" />
        </TouchableOpacity>
      )}

      <Modal visible={showAddModal} transparent animationType="slide">
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>Add Driver</Text>
            <TextInput style={styles.input} placeholder="Name" value={newDriver.name} onChangeText={(t) => setNewDriver({...newDriver, name: t})} />
            <TextInput style={styles.input} placeholder="Mobile Number" keyboardType="phone-pad" value={newDriver.mobile_number} onChangeText={(t) => setNewDriver({...newDriver, mobile_number: t})} />
            <TextInput style={styles.input} placeholder="Password" secureTextEntry value={newDriver.password} onChangeText={(t) => setNewDriver({...newDriver, password: t})} />
            <TextInput style={styles.input} placeholder="Vehicle Type (optional)" value={newDriver.vehicle_type} onChangeText={(t) => setNewDriver({...newDriver, vehicle_type: t})} />
            <AppButton title="Save Driver" onPress={handleAddDriver} />
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
  vehicle: { fontSize: 12, color: '#888', marginTop: 2 },
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
