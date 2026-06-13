import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl, TextInput, TouchableOpacity } from 'react-native';
import { getPartners, togglePartnerStatus } from '../../api/adminApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { Ionicons } from '@expo/vector-icons';

export const AdminPartnersScreen = ({ navigation }: any) => {
  const [data, setData] = useState<any[]>([]);
  const [filteredData, setFilteredData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [search, setSearch] = useState('');

  const loadData = async () => {
    try {
      const res = await getPartners();
      const list = res.data?.data || res.data || [];
      setData(list);
      setFilteredData(list);
    } catch (e) {
      setData([]);
      setFilteredData([]);
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

  const handleSearch = (text: string) => {
    setSearch(text);
    if (!text) {
      setFilteredData(data);
      return;
    }
    const lower = text.toLowerCase();
    const filtered = data.filter(item => 
      item.user?.name?.toLowerCase().includes(lower) || 
      item.user?.mobile_number?.includes(lower) ||
      item.user?.email?.toLowerCase().includes(lower) ||
      item.business_name?.toLowerCase().includes(lower)
    );
    setFilteredData(filtered);
  };

  const handleToggle = async (id: number) => {
    try {
      await togglePartnerStatus(id);
      loadData();
    } catch (e) {
      console.log('Error toggling status', e);
    }
  };

  if (loading) return <LoadingView message="Loading Partners..." />;

  const renderItem = ({ item }: { item: any }) => (
    <View style={styles.card}>
      <View style={styles.cardHeader}>
        <View>
          <Text style={styles.name}>{item.business_name || item.user?.name}</Text>
          <Text style={styles.detail}>{item.user?.name} | {item.user?.mobile_number}</Text>
          <Text style={styles.detail}>{item.user?.email}</Text>
        </View>
        <View style={[styles.badge, item.current_status === 'active' ? styles.badgeActive : styles.badgeInactive]}>
          <Text style={styles.badgeText}>{item.current_status}</Text>
        </View>
      </View>
      <View style={styles.cardFooter}>
        <Text style={styles.date}>Joined: {new Date(item.created_at).toLocaleDateString()}</Text>
        <View style={styles.actions}>
          <TouchableOpacity onPress={() => handleToggle(item.id)} style={[styles.actionBtn, styles.toggleBtn]}>
            <Text style={styles.actionBtnText}>{item.current_status === 'active' ? 'Deactivate' : 'Activate'}</Text>
          </TouchableOpacity>
          <TouchableOpacity onPress={() => navigation.navigate('AdminPartnerFormScreen', { id: item.id })} style={[styles.actionBtn, styles.editBtn]}>
            <Text style={[styles.actionBtnText, { color: '#FFF' }]}>Edit</Text>
          </TouchableOpacity>
          <TouchableOpacity onPress={() => navigation.navigate('AdminPartnerDetailScreen', { id: item.id })} style={[styles.actionBtn, styles.viewBtn]}>
            <Text style={[styles.actionBtnText, { color: '#FFF' }]}>View</Text>
          </TouchableOpacity>
        </View>
      </View>
    </View>
  );

  return (
    <View style={styles.container}>
      <View style={styles.searchRow}>
        <View style={styles.searchBar}>
          <Ionicons name="search" size={20} color="#888" />
          <TextInput
            style={styles.searchInput}
            placeholder="Search by business, phone..."
            value={search}
            onChangeText={handleSearch}
          />
        </View>
        <TouchableOpacity style={styles.addBtn} onPress={() => navigation.navigate('AdminPartnerFormScreen')}>
          <Ionicons name="add" size={24} color="#FFF" />
        </TouchableOpacity>
      </View>
      <FlatList
        data={filteredData}
        keyExtractor={(item, index) => item.id?.toString() || index.toString()}
        renderItem={renderItem}
        ListEmptyComponent={<EmptyState title="No Partners Found" />}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        contentContainerStyle={filteredData.length === 0 ? { flex: 1 } : { paddingBottom: 20 }}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA' },
  searchRow: { flexDirection: 'row', alignItems: 'center', margin: 16, gap: 12 },
  searchBar: { flex: 1, flexDirection: 'row', alignItems: 'center', backgroundColor: '#FFF', paddingHorizontal: 12, borderRadius: 8, height: 48, borderWidth: 1, borderColor: '#E0E0E0' },
  searchInput: { flex: 1, marginLeft: 8, fontSize: 16 },
  addBtn: { width: 48, height: 48, backgroundColor: '#28A745', borderRadius: 8, alignItems: 'center', justifyContent: 'center' },
  card: { backgroundColor: '#FFF', marginHorizontal: 16, marginBottom: 12, borderRadius: 12, padding: 16, elevation: 1 },
  cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
  name: { fontSize: 18, fontWeight: 'bold', color: '#333', marginBottom: 4 },
  detail: { fontSize: 14, color: '#666', marginBottom: 2 },
  badge: { paddingHorizontal: 8, paddingVertical: 4, borderRadius: 4 },
  badgeActive: { backgroundColor: '#E3FCEF' },
  badgeInactive: { backgroundColor: '#FFEBE6' },
  badgeText: { fontSize: 12, fontWeight: 'bold', color: '#333' },
  cardFooter: { marginTop: 12, paddingTop: 12, borderTopWidth: 1, borderTopColor: '#F0F0F0', flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  date: { fontSize: 12, color: '#999' },
  actions: { flexDirection: 'row', gap: 8 },
  actionBtn: { paddingHorizontal: 12, paddingVertical: 8, borderRadius: 6, minWidth: 60, alignItems: 'center' },
  toggleBtn: { backgroundColor: '#F0F0F0' },
  editBtn: { backgroundColor: '#FFC107' },
  viewBtn: { backgroundColor: '#007BFF' },
  actionBtnText: { fontSize: 14, fontWeight: 'bold', color: '#333' },
});
