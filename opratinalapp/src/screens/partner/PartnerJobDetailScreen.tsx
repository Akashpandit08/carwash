import React, { useEffect, useState } from 'react';
import { ScrollView, View, Text, StyleSheet, Alert } from 'react-native';
import { getPartnerJobDetail, updateJobStatus } from '../../api/partnerApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { StatusBadge } from '../../components/StatusBadge';
import { AppButton } from '../../components/AppButton';

export const PartnerJobDetailScreen = ({ route, navigation }: any) => {
  const { bookingId } = route.params;
  const [job, setJob] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [updating, setUpdating] = useState(false);

  const fetchDetail = async () => {
    try {
      const res = await getPartnerJobDetail(bookingId);
      setJob(res.data?.data || res.data);
    } catch (e) {
      console.log(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const unsubscribe = navigation.addListener('focus', () => {
      fetchDetail();
    });
    return unsubscribe;
  }, [navigation, bookingId]);

  const handleUpdateStatus = async (status: string) => {
    setUpdating(true);
    try {
      await updateJobStatus(bookingId, status);
      Alert.alert('Success', 'Status updated');
      fetchDetail();
    } catch (e: any) {
      Alert.alert('Error', e.response?.data?.message || 'Failed to update status');
    } finally {
      setUpdating(false);
    }
  };

  if (loading) return <LoadingView message="Loading Detail..." />;
  if (!job) return <EmptyState title="Job Not Found" />;

  return (
    <ScrollView style={styles.container}>
      <View style={styles.card}>
        <View style={styles.row}>
          <Text style={styles.title}>#{job.booking_no || job.id}</Text>
          <StatusBadge status={job.status || 'unknown'} />
        </View>
        <Text style={styles.label}>Customer: <Text style={styles.value}>{job.customer_name || job.customer?.name || 'N/A'}</Text></Text>
        <Text style={styles.label}>Service: <Text style={styles.value}>{job.service_name || job.service?.name || 'N/A'}</Text></Text>
        <Text style={styles.label}>Vehicle: <Text style={styles.value}>{job.vehicle_name || job.vehicle?.name || 'N/A'}</Text></Text>
      </View>

      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Assigned Worker</Text>
        <Text style={styles.label}>{job.worker?.name || 'Unassigned'}</Text>
        <AppButton 
          title="Assign/Change Worker" 
          type="secondary"
          onPress={() => navigation.navigate('PartnerWorkersScreen', { bookingId: job.id })} 
        />
      </View>

      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Actions</Text>
        {(() => {
          const { getPartnerActions, getWaitingMessage } = require('../../utils/statusFlow');
          const waitMsg = getWaitingMessage(job, 'partner');
          if (waitMsg) {
            return <Text style={{ color: '#856404', backgroundColor: '#FFF3CD', padding: 8, borderRadius: 4 }}>{waitMsg}</Text>;
          }
          const actions = getPartnerActions(job);
          if (!actions.length) return <Text style={styles.label}>No actions available</Text>;
          return actions.map((action: any, index: number) => (
            <AppButton 
              key={index} 
              title={action.title} 
              onPress={() => handleUpdateStatus(action.nextStatus)} 
              loading={updating} 
              style={{ marginTop: index > 0 ? 12 : 0 }} 
            />
          ));
        })()}
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  card: { backgroundColor: '#FFF', padding: 16, borderRadius: 8, marginBottom: 16, elevation: 1 },
  row: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 12 },
  title: { fontSize: 18, fontWeight: 'bold' },
  sectionTitle: { fontSize: 16, fontWeight: 'bold', marginBottom: 12, color: '#333' },
  label: { fontSize: 14, color: '#666', marginBottom: 6 },
  value: { color: '#000', fontWeight: '500' },
});
