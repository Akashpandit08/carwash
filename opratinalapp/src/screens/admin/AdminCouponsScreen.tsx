import React from 'react';
import { SafeScreen } from '../../components/SafeScreen';
import { View, Text, StyleSheet } from 'react-native';
import { getCoupons, createCoupon, updateCoupon, deleteCoupon } from '../../api/adminApi';
import { GenericCrudScreen, CrudField } from '../../components/GenericCrudScreen';

export const AdminCouponsScreen = () => {
  const fields: CrudField[] = [
    { name: 'code', label: 'Coupon Code (e.g., SAVE20)', type: 'text', required: true },
    { name: 'discount_value', label: 'Discount Amount/Percentage', type: 'number', required: true },
    { name: 'discount_type', label: 'Discount Type', type: 'select', options: [
      { label: 'Percentage', value: 'percentage' },
      { label: 'Fixed', value: 'fixed' }
    ], required: true },
    { name: 'min_order_amount', label: 'Min Order Amount', type: 'number' },
    { name: 'max_discount', label: 'Max Discount', type: 'number' },
    { name: 'usage_limit', label: 'Usage Limit', type: 'number' },
    { name: 'valid_until', label: 'Valid Until (YYYY-MM-DD)', type: 'text' },
    { name: 'is_active', label: 'Active', type: 'switch' }
  ];

  const renderCard = (item: any) => (
    <View style={styles.cardContent}>
      <View style={styles.header}>
        <Text style={styles.name}>{item.code}</Text>
        <Text style={[styles.badge, item.is_active ? styles.badgeActive : styles.badgeInactive]}>
          {item.is_active ? 'Active' : 'Inactive'}
        </Text>
      </View>
      <Text style={styles.detail}>Discount: {item.discount_type === 'percentage' ? `${item.discount_value}%` : `₹${item.discount_value}`}</Text>
      <Text style={styles.detail}>Valid Until: {item.valid_until ? new Date(item.valid_until).toLocaleDateString() : 'No Expiry'}</Text>
    </View>
  );

  return (
    <GenericCrudScreen
      title="Coupon"
      fields={fields}
      fetchApi={getCoupons}
      createApi={createCoupon}
      updateApi={updateCoupon}
      deleteApi={deleteCoupon}
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
