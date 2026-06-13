import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { getSlots, createSlot, updateSlot, deleteSlot } from '../../api/adminApi';
import { GenericCrudScreen, CrudField } from '../../components/GenericCrudScreen';

export const AdminSlotsScreen = () => {
  const fields: CrudField[] = [
    { name: 'date', label: 'Date (YYYY-MM-DD)', type: 'text', required: true },
    { name: 'start_time', label: 'Start Time (HH:mm:ss)', type: 'text', required: true },
    { name: 'end_time', label: 'End Time (HH:mm:ss)', type: 'text', required: true },
    { name: 'max_bookings', label: 'Max Bookings', type: 'number', required: true },
    { name: 'wash_type', label: 'Wash Type', type: 'select', options: [
      { label: 'Door to Door', value: 'door_to_door' },
      { label: 'Pickup & Drop', value: 'pickup_drop' },
      { label: 'Drive In', value: 'drive_in' }
    ] },
    { name: 'is_active', label: 'Active', type: 'switch' }
  ];

  const renderCard = (item: any) => (
    <View style={styles.cardContent}>
      <View style={styles.header}>
        <Text style={styles.name}>{item.date} {item.start_time} - {item.end_time}</Text>
        <Text style={[styles.badge, item.is_active ? styles.badgeActive : styles.badgeInactive]}>
          {item.is_active ? 'Active' : 'Inactive'}
        </Text>
      </View>
      <Text style={styles.detail}>Max Bookings: {item.max_bookings || 0}</Text>
      {item.wash_type && <Text style={styles.detail}>Wash Type: {item.wash_type.replace(/_/g, ' ')}</Text>}
    </View>
  );

  return (
    <GenericCrudScreen
      title="Slot"
      fields={fields}
      fetchApi={getSlots}
      createApi={createSlot}
      updateApi={updateSlot}
      deleteApi={deleteSlot}
      renderCard={renderCard}
    />
  );
};

const styles = StyleSheet.create({
  cardContent: { padding: 16 },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
  name: { fontSize: 18, fontWeight: 'bold', color: '#333' },
  badge: { paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6, fontSize: 12, fontWeight: 'bold' },
  badgeActive: { backgroundColor: '#E3FCEF', color: '#006644' },
  badgeInactive: { backgroundColor: '#FFEBE6', color: '#BF2600' },
  detail: { fontSize: 14, color: '#666', marginTop: 4 }
});
