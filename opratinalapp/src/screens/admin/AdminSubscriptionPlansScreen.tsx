import React from 'react';
import { SafeScreen } from '../../components/SafeScreen';
import { View, Text, StyleSheet } from 'react-native';
import { getSubscriptionPlans, createSubscriptionPlan, updateSubscriptionPlan, deleteSubscriptionPlan } from '../../api/adminApi';
import { GenericCrudScreen, CrudField } from '../../components/GenericCrudScreen';

export const AdminSubscriptionPlansScreen = () => {
  const fields: CrudField[] = [
    { name: 'name', label: 'Plan Name', type: 'text', required: true },
    { name: 'price', label: 'Price', type: 'number', required: true },
    { name: 'duration_days', label: 'Duration (Days)', type: 'number', required: true },
    { name: 'exterior_washes', label: 'Exterior Washes', type: 'number', required: true },
    { name: 'interior_washes', label: 'Interior Washes', type: 'number', required: true },
    { name: 'foam_washes', label: 'Foam Washes', type: 'number', required: true },
    { name: 'tyre_polish_included', label: 'Tyre Polish Included', type: 'switch' },
    { name: 'dashboard_wipe_included', label: 'Dashboard Wipe Included', type: 'switch' },
    { name: 'vacuum_included', label: 'Vacuum Included', type: 'switch' },
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
      <Text style={styles.detail}>Price: ₹{item.price}</Text>
      <Text style={styles.detail}>Duration: {item.duration_days} days</Text>
      <Text style={styles.detail}>Washes: Ext ({item.exterior_washes}), Int ({item.interior_washes}), Foam ({item.foam_washes})</Text>
    </View>
  );

  return (
    <GenericCrudScreen
      title="Subscription Plan"
      fields={fields}
      fetchApi={getSubscriptionPlans}
      createApi={createSubscriptionPlan}
      updateApi={updateSubscriptionPlan}
      deleteApi={deleteSubscriptionPlan}
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
