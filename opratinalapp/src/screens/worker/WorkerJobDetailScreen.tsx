import React, { useEffect, useState } from 'react';
import { ScrollView, View, Text, StyleSheet } from 'react-native';
import { getWorkerJobDetail } from '../../api/workerApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { StatusBadge } from '../../components/StatusBadge';
import { AppButton } from '../../components/AppButton';

export const WorkerJobDetailScreen = ({ route, navigation }: any) => {
  const { bookingId } = route.params;
  const [job, setJob] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  const fetchDetail = async () => {
    try {
      const res = await getWorkerJobDetail(bookingId);
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

  if (loading) return <LoadingView message="Loading Detail..." />;
  if (!job) return <EmptyState title="Job Not Found" />;

  const renderActions = () => {
    const { getWorkerActions, getWaitingMessage, canUploadBeforeImages, canUploadAfterImages } = require('../../utils/statusFlow');
    
    const waitMsg = getWaitingMessage(job, 'worker');
    if (waitMsg) {
      return (
        <View style={{ marginTop: 24, padding: 16, backgroundColor: '#FFF3CD', borderRadius: 8 }}>
          <Text style={{ color: '#856404' }}>{waitMsg}</Text>
        </View>
      );
    }

    const actions = getWorkerActions(job);
    if (!actions.length) return null;

    return (
      <View style={{ marginTop: 24 }}>
        <Text style={{ fontSize: 16, fontWeight: 'bold', marginBottom: 12, color: '#333' }}>Task Execution</Text>
        {actions.map((action: any, index: number) => {
          const isImageRequired = action.nextStatus === 'service_started' || action.nextStatus === 'service_completed' || action.nextStatus === 'ready_for_delivery';
          
          const handlePress = async () => {
            if (isImageRequired) {
              navigation.navigate('WorkerExecutionScreen', { job, nextStatus: action.nextStatus });
            } else {
              setLoading(true);
              try {
                const { updateJobStatus } = require('../../api/workerApi');
                await updateJobStatus(job.id, action.nextStatus);
                const { Alert } = require('react-native');
                Alert.alert('Success', `Status updated to ${action.title}`);
                fetchDetail();
              } catch (e: any) {
                const { Alert } = require('react-native');
                Alert.alert('Error', e.response?.data?.message || 'Failed to update status');
              } finally {
                setLoading(false);
              }
            }
          };

          return (
            <AppButton 
              key={index}
              title={action.title} 
              onPress={handlePress} 
              style={{ marginTop: 12 }}
            />
          );
        })}
      </View>
    );
  };

  return (
    <ScrollView style={styles.container}>
      <View style={styles.card}>
        <View style={styles.row}>
          <Text style={styles.title}>#{job.booking_no || job.id}</Text>
          <StatusBadge status={job.status || 'unknown'} />
        </View>
        <Text style={styles.label}>Service: <Text style={styles.value}>{job.service_name || job.service?.name || 'N/A'}</Text></Text>
        <Text style={styles.label}>Vehicle: <Text style={styles.value}>{job.vehicle_name || job.vehicle?.name || 'N/A'}</Text></Text>
      </View>

      {renderActions()}
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  card: { backgroundColor: '#FFF', padding: 16, borderRadius: 8, marginBottom: 16, elevation: 1 },
  row: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 12 },
  title: { fontSize: 18, fontWeight: 'bold' },
  label: { fontSize: 14, color: '#666', marginBottom: 6 },
  value: { color: '#000', fontWeight: '500' },
});
