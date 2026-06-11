import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl } from 'react-native';
import { getPartners, getWorkers, getPickupDrivers, getServices, getSlots, getCoupons, getReports } from '../../api/adminApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';

// Generic List Screen Builder
const createListScreen = (fetchFn: () => Promise<any>, title: string, renderItem: (item: any) => React.ReactElement) => {
  return () => {
    const [data, setData] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const loadData = async () => {
      try {
        const res = await fetchFn();
        setData(res.data?.data || res.data || []);
      } catch (e) {
        setData([]);
      } finally {
        setLoading(false);
        setRefreshing(false);
      }
    };

    useEffect(() => { loadData(); }, []);

    const onRefresh = () => {
      setRefreshing(true);
      loadData();
    };

    if (loading) return <LoadingView message={`Loading ${title}...`} />;

    return (
      <View style={styles.container}>
        <FlatList
          data={data}
          keyExtractor={(item, index) => item.id?.toString() || index.toString()}
          renderItem={({ item }) => renderItem(item)}
          ListEmptyComponent={<EmptyState title={`No ${title} Found`} />}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
          contentContainerStyle={data.length === 0 ? { flex: 1 } : { paddingBottom: 20 }}
        />
      </View>
    );
  };
};

// Renderers
const renderUserCard = (item: any) => (
  <View style={styles.card}>
    <Text style={styles.name}>{item.name}</Text>
    <Text style={styles.detail}>{item.email}</Text>
    <Text style={styles.detail}>{item.phone || 'No phone'}</Text>
  </View>
);

const renderServiceCard = (item: any) => (
  <View style={styles.card}>
    <Text style={styles.name}>{item.name}</Text>
    <Text style={styles.detail}>Price: ${item.price}</Text>
    <Text style={styles.detail}>{item.description}</Text>
  </View>
);

const renderSlotCard = (item: any) => (
  <View style={styles.card}>
    <Text style={styles.name}>{item.time || item.name}</Text>
    <Text style={styles.detail}>Status: {item.is_active ? 'Active' : 'Inactive'}</Text>
  </View>
);

const renderCouponCard = (item: any) => (
  <View style={styles.card}>
    <Text style={styles.name}>{item.code}</Text>
    <Text style={styles.detail}>Discount: {item.discount}%</Text>
  </View>
);

const renderReportCard = (item: any) => (
  <View style={styles.card}>
    <Text style={styles.name}>{item.title || 'Report'}</Text>
    <Text style={styles.detail}>{item.summary || JSON.stringify(item)}</Text>
  </View>
);

export const AdminPartnersScreen = createListScreen(getPartners, 'Partners', renderUserCard);
export const AdminWorkersScreen = createListScreen(getWorkers, 'Workers', renderUserCard);
export const AdminDriversScreen = createListScreen(getPickupDrivers, 'Drivers', renderUserCard);
export const AdminServicesScreen = createListScreen(getServices, 'Services', renderServiceCard);
export const AdminSlotsScreen = createListScreen(getSlots, 'Slots', renderSlotCard);
export const AdminCouponsScreen = createListScreen(getCoupons, 'Coupons', renderCouponCard);
export const AdminReportsScreen = createListScreen(getReports, 'Reports', renderReportCard);

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  card: { backgroundColor: '#FFF', padding: 16, borderRadius: 8, marginBottom: 12, elevation: 1 },
  name: { fontSize: 16, fontWeight: 'bold', color: '#333' },
  detail: { fontSize: 14, color: '#666', marginTop: 4 },
});
