import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, Alert, TouchableOpacity } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { assignPickupDriver, assignPartner, assignWorker, getPickupDrivers, getPartners, getWorkers, getBookingDetail } from '../../api/adminApi';
import { AppButton } from '../../components/AppButton';
import { LoadingView } from '../../components/LoadingView';
import { Ionicons } from '@expo/vector-icons';

export const AssignTeamScreen = ({ route, navigation }: any) => {
  const { booking: initialBooking } = route.params;
  const bookingId = initialBooking?.id;

  const insets = useSafeAreaInsets();

  const [booking, setBooking] = useState<any>(initialBooking);
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
        const [drv, prt, wrk, bkng] = await Promise.all([
          getPickupDrivers().catch(() => ({ data: { data: [] } })),
          getPartners().catch(() => ({ data: { data: [] } })),
          getWorkers().catch(() => ({ data: { data: [] } })),
          getBookingDetail(bookingId).catch(() => ({ data: { data: initialBooking } }))
        ]);
        
        setDrivers(drv.data?.data || []);
        setPartners(prt.data?.data || []);
        setWorkers(wrk.data?.data || []);
        if (bkng.data?.data) {
           setBooking(bkng.data.data);
           // Pre-select if already assigned
           if (bkng.data.data.pickup_driver_id) setSelectedDriver(bkng.data.data.pickup_driver_id);
           if (bkng.data.data.partner_id) setSelectedPartner(bkng.data.data.partner_id);
           if (bkng.data.data.worker_id) setSelectedWorker(bkng.data.data.worker_id);
        }
      } catch (e) {
        console.log(e);
      } finally {
        setLoadingData(false);
      }
    };
    fetchData();
  }, [bookingId]);

  const handleAssign = async () => {
    if (!canSave) return;
    
    setSaving(true);
    let successCount = 0;
    try {
      if (selectedDriver && selectedDriver !== booking.pickup_driver_id) {
        await assignPickupDriver(bookingId, selectedDriver);
        successCount++;
      }
      if (selectedPartner && selectedPartner !== booking.partner_id) {
        await assignPartner(bookingId, selectedPartner);
        successCount++;
      }
      if (selectedWorker && selectedWorker !== booking.worker_id) {
        await assignWorker(bookingId, selectedWorker);
        successCount++;
      }
      
      if (successCount > 0) {
        Alert.alert('Success', 'Assignments updated successfully!', [
          { text: 'OK', onPress: () => {
             // Go back and signal refresh if possible, or simply go back
             navigation.goBack();
          }}
        ]);
      } else {
        Alert.alert('Info', 'No changes were made.');
        navigation.goBack();
      }
    } catch (e: any) {
      Alert.alert('Error', e.response?.data?.message || 'Failed to assign team');
    } finally {
      setSaving(false);
    }
  };

  if (loadingData) return <LoadingView message="Loading teams..." />;

  const isPickupDrop = booking?.service_mode === 'pickup_drop' || booking?.wash_type === 'pickup_drop';

  const canSave = (isPickupDrop ? (selectedDriver || selectedPartner || selectedWorker) : (selectedPartner || selectedWorker));

  const renderSelection = (title: string, list: any[], selected: any, setSelected: any) => (
    <View style={styles.section}>
      <Text style={styles.sectionTitle}>{title}</Text>
      {list.length === 0 ? <Text style={styles.emptyText}>No active {title.toLowerCase()} available.</Text> : null}
      <View style={styles.listContainer}>
        {list.map(item => {
          const isSelected = selected === item.id;
          return (
            <TouchableOpacity 
              key={item.id} 
              style={[styles.itemCard, isSelected && styles.itemCardSelected]}
              onPress={() => setSelected(item.id)}
            >
              <View style={styles.cardHeader}>
                <Ionicons name="person-circle-outline" size={32} color={isSelected ? '#007BFF' : '#666'} />
                <View style={styles.cardInfo}>
                  <Text style={[styles.itemName, isSelected && styles.itemNameSelected]}>
                    {item.name}
                  </Text>
                  {item.mobile_number && <Text style={styles.itemPhone}>{item.mobile_number}</Text>}
                </View>
                <View style={[styles.radioOuter, isSelected && styles.radioOuterSelected]}>
                  {isSelected && <View style={styles.radioInner} />}
                </View>
              </View>
              {item.service_city_name && (
                <Text style={styles.itemCity}>Area: {item.service_city_name}</Text>
              )}
            </TouchableOpacity>
          );
        })}
      </View>
    </View>
  );

  return (
    <View style={[styles.container, { paddingBottom: insets.bottom }]}>
      <ScrollView style={styles.scroll}>
        <View style={styles.summaryCard}>
          <Text style={styles.summaryTitle}>Booking #{booking?.booking_no || bookingId}</Text>
          <Text style={styles.summaryText}>Customer: {booking?.user?.name || 'N/A'}</Text>
          <Text style={styles.summaryText}>Service: {booking?.service?.name || 'N/A'}</Text>
          <Text style={styles.summaryText}>Type: {isPickupDrop ? 'Pickup & Drop' : 'Doorstep'}</Text>
          {booking?.service_city?.name && <Text style={styles.summaryText}>City: {booking.service_city.name}</Text>}
          <Text style={styles.summaryText}>Date: {booking?.booking_date} {booking?.slot_time}</Text>
          <Text style={[styles.summaryText, { fontWeight: 'bold', marginTop: 4, color: '#FF9800' }]}>Status: {booking?.status?.replace(/_/g, ' ').toUpperCase()}</Text>
        </View>
        
        {isPickupDrop && renderSelection('Pickup Drivers', drivers, selectedDriver, setSelectedDriver)}
        {renderSelection('Partners', partners, selectedPartner, setSelectedPartner)}
        {renderSelection('Workers', workers, selectedWorker, setSelectedWorker)}

      </ScrollView>
      <View style={styles.footer}>
        <AppButton 
          title="Save Assignments" 
          onPress={handleAssign} 
          loading={saving} 
          disabled={!canSave || saving}
        />
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA' },
  scroll: { flex: 1, padding: 16 },
  summaryCard: { backgroundColor: '#FFF', padding: 16, borderRadius: 12, marginBottom: 20, elevation: 2, shadowColor: '#000', shadowOpacity: 0.1, shadowRadius: 4 },
  summaryTitle: { fontSize: 18, fontWeight: 'bold', marginBottom: 10, color: '#333' },
  summaryText: { fontSize: 14, color: '#555', marginBottom: 4 },
  section: { marginBottom: 24 },
  sectionTitle: { fontSize: 16, fontWeight: '700', marginBottom: 12, color: '#333' },
  emptyText: { color: '#999', fontStyle: 'italic', marginLeft: 4 },
  listContainer: { flexDirection: 'column', gap: 10 },
  itemCard: { padding: 12, borderRadius: 10, borderWidth: 1, borderColor: '#E0E0E0', backgroundColor: '#FFF' },
  itemCardSelected: { borderColor: '#007BFF', backgroundColor: '#F0F8FF' },
  cardHeader: { flexDirection: 'row', alignItems: 'center' },
  cardInfo: { flex: 1, marginLeft: 10 },
  itemName: { fontSize: 15, fontWeight: '600', color: '#333' },
  itemNameSelected: { color: '#007BFF' },
  itemPhone: { fontSize: 13, color: '#777', marginTop: 2 },
  itemCity: { fontSize: 12, color: '#888', marginTop: 8, marginLeft: 42 },
  radioOuter: { width: 22, height: 22, borderRadius: 11, borderWidth: 2, borderColor: '#CCC', alignItems: 'center', justifyContent: 'center' },
  radioOuterSelected: { borderColor: '#007BFF' },
  radioInner: { width: 10, height: 10, borderRadius: 5, backgroundColor: '#007BFF' },
  footer: { padding: 16, backgroundColor: '#FFF', borderTopWidth: 1, borderTopColor: '#EEE' },
});
