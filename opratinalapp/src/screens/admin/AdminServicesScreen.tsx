import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { getServices, createService, updateService, deleteService } from '../../api/adminApi';
import { GenericCrudScreen } from '../../components/GenericCrudScreen';

export const AdminServicesScreen = () => {
  const fields: any[] = [
    { name: 'name', label: 'Service Name', type: 'text', required: true },
    { name: 'price', label: 'Price', type: 'number', required: true },
    { name: 'duration_minutes', label: 'Duration (mins)', type: 'number', required: true },
    { name: 'vehicle_types', label: 'Vehicle Types', type: 'multi-select', options: [
      { label: 'Hatchback', value: 'Hatchback' },
      { label: 'Sedan', value: 'Sedan' },
      { label: 'SUV', value: 'SUV' },
      { label: 'Bike', value: 'Bike' }
    ], required: true },
    { name: 'wash_type', label: 'Wash Type', type: 'select', options: [
      { label: 'Door to Door', value: 'door_to_door' },
      { label: 'Pickup & Drop', value: 'pickup_drop' },
      { label: 'Drive In', value: 'drive_in' }
    ] },
    { name: 'is_active', label: 'Active Status', type: 'switch' }
  ];

  const renderCard = (item: any) => (
    <View style={styles.cardContent}>
      <View style={styles.header}>
        <Text style={styles.name}>{item.name}</Text>
        <Text style={[styles.badge, item.is_active ? styles.badgeActive : styles.badgeInactive]}>
          {item.is_active ? 'Active' : 'Inactive'}
        </Text>
      </View>
      <Text style={styles.detail}>Price: ₹{item.price}</Text>
      <Text style={styles.detail}>Duration: {item.duration_minutes} mins</Text>
      <Text style={styles.detail}>Vehicle Types: {Array.isArray(item.vehicle_types) ? item.vehicle_types.join(', ') : item.vehicle_types}</Text>
      {item.wash_type && <Text style={styles.detail}>Wash Type: {item.wash_type.replace(/_/g, ' ')}</Text>}
    </View>
  );

  return (
    <GenericCrudScreen
      title="Service"
      fields={fields}
      fetchApi={getServices}
      createApi={createService}
      updateApi={updateService}
      deleteApi={deleteService}
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
