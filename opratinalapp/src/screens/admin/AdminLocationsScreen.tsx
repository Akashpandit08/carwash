import React from 'react';
import { SafeScreen } from '../../components/SafeScreen';
import { View, Text, StyleSheet } from 'react-native';
import { getCities, createCity, updateCity, deleteCity } from '../../api/adminApi';
import { GenericCrudScreen, CrudField } from '../../components/GenericCrudScreen';

export const AdminLocationsScreen = () => {
  const fields: CrudField[] = [
    { name: 'name', label: 'City Name', type: 'text', required: true },
    { name: 'state', label: 'State', type: 'text', required: true },
    { name: 'status', label: 'Status', type: 'select', options: [
      { label: 'Active', value: 'active' },
      { label: 'Inactive', value: 'inactive' }
    ], required: true }
  ];

  const renderCard = (item: any) => (
    <View style={styles.cardContent}>
      <View style={styles.header}>
        <Text style={styles.name}>{item.name}</Text>
        <Text style={[styles.badge, item.status === 'active' ? styles.badgeActive : styles.badgeInactive]}>
          {item.status}
        </Text>
      </View>
      <Text style={styles.detail}>State: {item.state}</Text>
    </View>
  );

  return (
    <GenericCrudScreen
      title="Location"
      fields={fields}
      fetchApi={getCities}
      createApi={createCity}
      updateApi={updateCity}
      deleteApi={deleteCity}
      renderCard={renderCard}
    />
  );
};

const styles = StyleSheet.create({
  cardContent: { padding: 16 },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
  name: { fontSize: 18, fontWeight: 'bold', color: '#333' },
  badge: { paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6, fontSize: 12, fontWeight: 'bold', textTransform: 'capitalize' },
  badgeActive: { backgroundColor: '#E3FCEF', color: '#006644' },
  badgeInactive: { backgroundColor: '#FFEBE6', color: '#BF2600' },
  detail: { fontSize: 14, color: '#666', marginTop: 4 }
});
