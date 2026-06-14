import React, { useEffect, useState } from 'react';
import { SafeScreen } from '../../components/SafeScreen';
import { View, Text, StyleSheet } from 'react-native';
import { getCityAdmins, createCityAdmin, updateCityAdmin, deleteCityAdmin, getCities } from '../../api/adminApi';
import { GenericCrudScreen, CrudField } from '../../components/GenericCrudScreen';

export const AdminCityAdminsScreen = () => {
  const [cities, setCities] = useState<any[]>([]);

  useEffect(() => {
    getCities().then(res => {
      const data = res.data?.data || res.data || [];
      const arr = Array.isArray(data) ? data : data.data || [];
      setCities(arr.map((c: any) => ({ label: c.name, value: c.id })));
    }).catch(console.error);
  }, []);

  const fields: CrudField[] = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'email', label: 'Email', type: 'text', required: true },
    { name: 'mobile_number', label: 'Mobile Number', type: 'text', required: true },
    { name: 'password', label: 'Password (leave blank for default)', type: 'text' },
    { name: 'service_city_id', label: 'Assign City', type: 'select', options: cities, required: true }
  ];

  const renderCard = (item: any) => (
    <View style={styles.cardContent}>
      <View style={styles.header}>
        <Text style={styles.name}>{item.name}</Text>
        <Text style={styles.badge}>{item.service_city_name || 'No City Assigned'}</Text>
      </View>
      <Text style={styles.detail}>Email: {item.email}</Text>
      <Text style={styles.detail}>Mobile: {item.mobile_number}</Text>
    </View>
  );

  return (
    <GenericCrudScreen
      title="City Admin"
      fields={fields}
      fetchApi={getCityAdmins}
      createApi={createCityAdmin}
      updateApi={updateCityAdmin}
      deleteApi={deleteCityAdmin}
      renderCard={renderCard}
    />
  );
};

const styles = StyleSheet.create({
  cardContent: { padding: 16 },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
  name: { fontSize: 18, fontWeight: 'bold', color: '#333' },
  badge: { backgroundColor: '#EAF4FF', color: '#007BFF', fontSize: 12, fontWeight: 'bold', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6, overflow: 'hidden' },
  detail: { fontSize: 14, color: '#666', marginTop: 4 }
});
