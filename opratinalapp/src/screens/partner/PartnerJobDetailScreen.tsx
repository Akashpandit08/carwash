import React, { useEffect, useState } from 'react';
import { ScrollView, View, Text, StyleSheet, Alert } from 'react-native';
import apiClient from '../../api/client';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { StatusBadge } from '../../components/StatusBadge';
import { AppButton } from '../../components/AppButton';

function getPartnerActions(job: any) {
  const washType = job.wash_type;
  const status = job.status;

  const actions = [];

  if (washType === 'door_to_door') {
    if (status === 'partner_assigned' || status === 'accepted_by_partner') {
      actions.push('assign_worker');
    }
  }

  if (washType === 'pickup_wash' || washType === 'pickup_drop') {
    if (status === 'partner_assigned') {
      actions.push('accept_booking');
    }

    if (
      status === 'pickup_driver_assigned' ||
      status === 'driver_on_the_way' ||
      status === 'pickup_started' ||
      status === 'vehicle_picked_up' ||
      status === 'car_picked_up'
    ) {
      actions.push('track_pickup_driver');
    }

    if (status === 'reached_partner' || status === 'vehicle_reached_garage') {
      actions.push('accept_vehicle_start_service');
    }

    if (status === 'service_started' || status === 'wash_started') {
      actions.push('complete_service');
    }

    if (status === 'service_completed' || status === 'wash_completed') {
      actions.push('handover_to_driver');
    }
  }

  if (washType === 'drive_in') {
    if (status === 'confirmed' || status === 'partner_assigned') {
      actions.push('mark_customer_arrived');
    }

    if (status === 'customer_arrived' || status === 'reached_partner') {
      actions.push('start_service');
    }

    if (status === 'service_started' || status === 'wash_started') {
      actions.push('complete_service');
    }

    if (job.payment_mode === 'cash' && (job.payment_status === 'pending' || !job.payment_status) && status === 'service_completed') {
      actions.push('collect_cash');
    }
  }

  return actions;
}

export const PartnerJobDetailScreen = ({ route, navigation }: any) => {
  const { bookingId } = route.params;
  const [job, setJob] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [updating, setUpdating] = useState(false);

  const fetchDetail = async () => {
    try {
      const res = await apiClient.get(`/partner/jobs/${bookingId}`);
      setJob(res.data?.data || res.data);
    } catch (e) {
      console.log('Failed to fetch job detail', e);
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

  const handleAction = async (actionId: string) => {
    if (actionId === 'assign_worker') {
      navigation.navigate('PartnerWorkersScreen', { bookingId: job.id, isSelectionMode: true });
      return;
    }
    
    if (actionId === 'track_pickup_driver' || actionId === 'track_worker') {
      Alert.alert('Tracking', 'Live tracking map opens here.');
      return;
    }

    let endpoint = '';
    let confirmMsg = '';

    switch (actionId) {
      case 'accept_booking':
        endpoint = `/partner/jobs/${bookingId}/accept`;
        confirmMsg = 'Are you sure you want to accept this booking?';
        break;
      case 'accept_vehicle_start_service':
        endpoint = `/partner/jobs/${bookingId}/accept-vehicle`;
        confirmMsg = 'Accept the vehicle at the garage?';
        break;
      case 'start_service':
        endpoint = `/partner/jobs/${bookingId}/start-service`;
        confirmMsg = 'Start the washing service now?';
        break;
      case 'complete_service':
        endpoint = `/partner/jobs/${bookingId}/complete-service`;
        confirmMsg = 'Mark service as complete?';
        break;
      case 'handover_to_driver':
        endpoint = `/partner/jobs/${bookingId}/handover-to-driver`;
        confirmMsg = 'Handover vehicle to delivery driver?';
        break;
      case 'mark_customer_arrived':
        endpoint = `/partner/jobs/${bookingId}/mark-customer-arrived`;
        confirmMsg = 'Mark customer as arrived?';
        break;
      case 'collect_cash':
        endpoint = `/partner/jobs/${bookingId}/collect-cash`;
        confirmMsg = `Collect ₹${job.total_amount} in Cash?`;
        break;
    }

    if (!endpoint) return;

    Alert.alert('Confirm', confirmMsg, [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Proceed',
        onPress: async () => {
          setUpdating(true);
          try {
            await apiClient.post(endpoint);
            Alert.alert('Success', 'Action completed successfully');
            fetchDetail();
          } catch (e: any) {
            Alert.alert('Error', e.response?.data?.message || 'Action failed');
          } finally {
            setUpdating(false);
          }
        }
      }
    ]);
  };

  const renderActionButtons = () => {
    const actions = getPartnerActions(job);
    if (!actions.length) return <Text style={styles.label}>Waiting for next step...</Text>;

    const buttonMap: Record<string, string> = {
      assign_worker: 'Assign Worker',
      accept_booking: 'Accept Booking',
      track_pickup_driver: 'Track Driver',
      accept_vehicle_start_service: 'Accept Vehicle & Start Service',
      start_service: 'Start Service',
      complete_service: 'Complete Service',
      handover_to_driver: 'Handover to Driver',
      mark_customer_arrived: 'Mark Customer Arrived',
      collect_cash: 'Collect Cash',
    };

    return actions.map((actionId) => (
      <AppButton
        key={actionId}
        title={buttonMap[actionId] || actionId}
        onPress={() => handleAction(actionId)}
        loading={updating}
        style={{ marginBottom: 12 }}
      />
    ));
  };

  if (loading) return <LoadingView message="Loading Detail..." />;
  if (!job) return <EmptyState title="Job Not Found" />;

  return (
    <ScrollView style={styles.container}>
      <View style={styles.card}>
        <View style={styles.row}>
          <Text style={styles.title}>#{job.booking_number || job.id}</Text>
          <StatusBadge status={job.status || 'unknown'} />
        </View>
        <Text style={styles.label}>Wash Type: <Text style={styles.value}>{job.wash_type}</Text></Text>
        <Text style={styles.label}>Customer: <Text style={styles.value}>{job.customer?.name || 'N/A'}</Text></Text>
        <Text style={styles.label}>Service: <Text style={styles.value}>{job.service?.name || 'N/A'}</Text></Text>
        <Text style={styles.label}>Vehicle: <Text style={styles.value}>{job.vehicle?.vehicle_type || 'N/A'}</Text></Text>
        <Text style={styles.label}>Payment: <Text style={styles.value}>{job.payment_mode} ({job.payment_status})</Text></Text>
        <Text style={styles.label}>Amount: <Text style={styles.value}>₹{job.total_amount}</Text></Text>
      </View>

      {job.worker && (
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Assigned Worker</Text>
          <Text style={styles.label}>{job.worker?.name}</Text>
          <Text style={styles.label}>{job.worker?.mobile_number}</Text>
        </View>
      )}

      {job.pickup_driver && (
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Pickup Driver</Text>
          <Text style={styles.label}>{job.pickup_driver?.name}</Text>
          <Text style={styles.label}>{job.pickup_driver?.mobile_number}</Text>
        </View>
      )}

      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Actions</Text>
        {renderActionButtons()}
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
