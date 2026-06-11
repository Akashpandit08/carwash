import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, Alert, TouchableOpacity } from 'react-native';
import { assignPickupDriver, assignPartner, assignWorker, getPickupDrivers, getPartners, getWorkers } from '../../api/adminApi';
import { AppButton } from '../../components/AppButton';
import { LoadingView } from '../../components/LoadingView';

export const AssignTeamScreen = ({ route, navigation }: any) => {
  const { booking } = route.params;
  const bookingId = booking?.id;

  const [drivers, setDrivers] = useState<any[]>([]);
  const [partners, setPartners] = useState<any[]>([]);
  const [workers, setWorkers] = useState<any[]>([]);
  
  const [selectedDriver, setSelectedDriver] = useState<any>(null);
  const [selectedPartner, setSelectedPartner] = useState<any>(null);
  const [selectedWorker, setSelectedWorker] = useState<any>(null);
  
  const [loadingData, setLoadingData] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [drv, prt, wrk] = await Promise.all([
          getPickupDrivers().catch(() => ({ data: { data: [] } })),
          getPartners().catch(() => ({ data: { data: [] } })),
          getWorkers().catch(() => ({ data: { data: [] } }))
        ]);
        setDrivers(drv.data?.data || []);
        setPartners(prt.data?.data || []);
        setWorkers(wrk.data?.data || []);
      } catch (e) {
        console.log(e);
      } finally {
        setLoadingData(false);
      }
    };
    fetchData();
  }, []);

  const handleAssign = async () => {
    setSaving(true);
    try {
      if (selectedDriver) await assignPickupDriver(bookingId, selectedDriver);
      if (selectedPartner) await assignPartner(bookingId, selectedPartner);
      if (selectedWorker) await assignWorker(bookingId, selectedWorker);
      
      Alert.alert('Success', 'Assignments updated successfully!', [
        { text: 'OK', onPress: () => navigation.goBack() }
      ]);
    } catch (e: any) {
      Alert.alert('Error', e.response?.data?.message || 'Failed to assign team');
    } finally {
      setSaving(false);
    }
  };

  if (loadingData) return <LoadingView message="Loading teams..." />;

  const renderSelection = (title: string, list: any[], selected: any, setSelected: any) => (
    <View style={styles.section}>
      <Text style={styles.sectionTitle}>{title}</Text>
      {list.length === 0 ? <Text style={styles.emptyText}>No available options.</Text> : null}
      <View style={styles.grid}>
        {list.map(item => (
          <TouchableOpacity 
            key={item.id} 
            style={[styles.item, selected === item.id && styles.itemSelected]}
            onPress={() => setSelected(item.id)}
          >
            <Text style={[styles.itemText, selected === item.id && styles.itemTextSelected]}>
              {item.name}
            </Text>
          </TouchableOpacity>
        ))}
      </View>
    </View>
  );

  return (
    <ScrollView style={styles.container}>
      <Text style={styles.title}>Assign Team for #{booking?.booking_no || bookingId}</Text>
      
      {renderSelection('Pickup Drivers', drivers, selectedDriver, setSelectedDriver)}
      {renderSelection('Partners', partners, selectedPartner, setSelectedPartner)}
      {renderSelection('Workers', workers, selectedWorker, setSelectedWorker)}

      <AppButton title="Save Assignments" onPress={handleAssign} loading={saving} style={{ marginTop: 24, marginBottom: 40 }} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FFF', padding: 16 },
  title: { fontSize: 18, fontWeight: 'bold', marginBottom: 20 },
  section: { marginBottom: 20 },
  sectionTitle: { fontSize: 16, fontWeight: '600', marginBottom: 8, color: '#333' },
  emptyText: { color: '#999', fontStyle: 'italic' },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  item: { paddingVertical: 8, paddingHorizontal: 12, borderRadius: 20, borderWidth: 1, borderColor: '#CCC', backgroundColor: '#F9F9F9' },
  itemSelected: { borderColor: '#007BFF', backgroundColor: '#007BFF' },
  itemText: { fontSize: 14, color: '#333' },
  itemTextSelected: { color: '#FFF', fontWeight: 'bold' },
});
