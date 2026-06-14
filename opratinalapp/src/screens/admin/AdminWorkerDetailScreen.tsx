import React, { useEffect, useState } from 'react';
import { SafeScreen } from '../../components/SafeScreen';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert } from 'react-native';
import { getWorkerDetail, toggleWorkerStatus } from '../../api/adminApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { Ionicons } from '@expo/vector-icons';

export const AdminWorkerDetailScreen = ({ route, navigation }: any) => {
  const { id } = route.params;
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  const loadData = async () => {
    try {
      const res = await getWorkerDetail(id);
      setData(res.data?.data || res.data || null);
    } catch (e) {
      console.log(e);
      setData(null);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { loadData(); }, [id]);

  const handleToggle = async () => {
    try {
      await toggleWorkerStatus(id);
      loadData();
    } catch (e) {
      Alert.alert('Error', 'Failed to update status');
    }
  };

  if (loading) return <LoadingView message="Loading Detail..." />;
  if (!data) return <EmptyState title="Worker Not Found" />;

  return (
    <SafeScreen scrollable style={styles.container}>
      {/* Profile Section */}
      <View style={styles.section}>
        <View style={styles.headerRow}>
          <Text style={styles.sectionTitle}>Profile Details</Text>
          <View style={[styles.badge, data.current_status === 'active' ? styles.badgeActive : styles.badgeInactive]}>
            <Text style={styles.badgeText}>{data.current_status}</Text>
          </View>
        </View>
        
        <View style={styles.row}>
          <Ionicons name="person-outline" size={20} color="#666" style={styles.icon} />
          <Text style={styles.text}>{data.user?.name}</Text>
        </View>
        <View style={styles.row}>
          <Ionicons name="call-outline" size={20} color="#666" style={styles.icon} />
          <Text style={styles.text}>{data.user?.mobile_number}</Text>
        </View>
        <View style={styles.row}>
          <Ionicons name="mail-outline" size={20} color="#666" style={styles.icon} />
          <Text style={styles.text}>{data.user?.email || 'No email'}</Text>
        </View>
        <View style={styles.row}>
          <Ionicons name="calendar-outline" size={20} color="#666" style={styles.icon} />
          <Text style={styles.text}>Joined {new Date(data.created_at).toLocaleDateString()}</Text>
        </View>
      </View>

      {/* Location / Zone Section */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Location & Zone</Text>
        <View style={styles.row}>
          <Ionicons name="location-outline" size={20} color="#666" style={styles.icon} />
          <Text style={styles.text}>Lat: {data.latitude || 'N/A'}, Lng: {data.longitude || 'N/A'}</Text>
        </View>
        <View style={styles.row}>
          <Ionicons name="map-outline" size={20} color="#666" style={styles.icon} />
          <Text style={styles.text}>Service Area: {data.service_area || 'Not set'}</Text>
        </View>
        {data.partner && (
          <View style={styles.row}>
            <Ionicons name="business-outline" size={20} color="#666" style={styles.icon} />
            <Text style={styles.text}>Assigned to Partner: {data.partner?.name}</Text>
          </View>
        )}
      </View>

      {/* Work Section */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Work Stats</Text>
        <View style={styles.row}>
          <Ionicons name="briefcase-outline" size={20} color="#666" style={styles.icon} />
          <Text style={styles.text}>Total Jobs: {data.total_jobs || 0}</Text>
        </View>
        <View style={styles.row}>
          <Ionicons name="star-outline" size={20} color="#666" style={styles.icon} />
          <Text style={styles.text}>Rating: {data.rating || 'No ratings'}</Text>
        </View>
      </View>

      {/* Actions */}
      <View style={styles.actions}>
        <TouchableOpacity style={styles.actionBtn} onPress={handleToggle}>
          <Ionicons name="power-outline" size={20} color="#FFF" />
          <Text style={styles.actionBtnText}>
            {data.current_status === 'active' ? 'Deactivate Worker' : 'Activate Worker'}
          </Text>
        </TouchableOpacity>
      </View>
    </SafeScreen>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA' },
  section: { backgroundColor: '#FFF', padding: 20, marginBottom: 16, borderBottomWidth: 1, borderBottomColor: '#EEE' },
  headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
  sectionTitle: { fontSize: 18, fontWeight: 'bold', color: '#333' },
  badge: { paddingHorizontal: 12, paddingVertical: 6, borderRadius: 6 },
  badgeActive: { backgroundColor: '#E3FCEF' },
  badgeInactive: { backgroundColor: '#FFEBE6' },
  badgeText: { fontSize: 14, fontWeight: 'bold', color: '#333' },
  row: { flexDirection: 'row', alignItems: 'center', marginBottom: 12 },
  icon: { marginRight: 12 },
  text: { fontSize: 16, color: '#444', flex: 1 },
  actions: { padding: 20 },
  actionBtn: { backgroundColor: '#007BFF', flexDirection: 'row', alignItems: 'center', justifyContent: 'center', padding: 16, borderRadius: 8, gap: 8 },
  actionBtnText: { color: '#FFF', fontSize: 16, fontWeight: 'bold' },
});
