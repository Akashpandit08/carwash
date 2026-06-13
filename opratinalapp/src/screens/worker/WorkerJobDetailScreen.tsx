import React, { useEffect, useState } from 'react';
import { Alert, RefreshControl, ScrollView, StyleSheet, Text, View } from 'react-native';
import { getWorkerAction } from '../../utils/statusFlow';
import { destinationForJob, openWhatsApp } from '../../utils/jobContact';
import { asLatLng } from '../../utils/maps';
import { getWorkerJobDetail, postWorkerAction } from '../../api/workerApi';
import { getCurrentCoords, sendLiveLocation, startLiveTracking, stopLiveTracking } from '../../services/locationTracking';
import { syncPendingUploads } from '../../services/offlineUploadQueue';
import { useNetworkStatus } from '../../hooks/useNetworkStatus';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { StatusBadge } from '../../components/StatusBadge';
import { AppButton } from '../../components/AppButton';
import { RouteMap } from '../../components/maps/RouteMap';
import { MapActionButtons } from '../../components/maps/MapActionButtons';

export const WorkerJobDetailScreen = ({ route, navigation }: any) => {
  const { bookingId } = route.params;
  const isOnline = useNetworkStatus();
  const [job, setJob] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [acting, setActing] = useState(false);
  const [currentLocation, setCurrentLocation] = useState<any>(null);
  const [isTracking, setIsTracking] = useState(false);

  const fetchDetail = async () => {
    try {
      const res = await getWorkerJobDetail(bookingId);
      setJob(res.data?.data || res.data);
      if (isOnline) syncPendingUploads();
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    const unsubscribe = navigation.addListener('focus', fetchDetail);
    return unsubscribe;
  }, [navigation, bookingId, isOnline]);

  useEffect(() => {
    if (!job) return;
    const activeStatuses = ['worker_assigned', 'worker_on_the_way', 'reached_location', 'service_started'];
    
    if (activeStatuses.includes(job.status)) {
      setIsTracking(true);
      startLiveTracking('worker', () => true, job.id);
    } else {
      setIsTracking(false);
      stopLiveTracking();
    }
    
    return () => {
      stopLiveTracking();
    };
  }, [job?.status, job?.id]);

  if (loading) return <LoadingView message="Loading job..." />;
  if (!job) return <EmptyState title="Job Not Found" />;

  const action = getWorkerAction(job);
  const customer = job.customer || job.user || {};
  const phone = customer.mobile_number || customer.phone || job.customer_phone || job.phone;
  const destination = destinationForJob(job);
  const destinationLocation = asLatLng(destination.latitude, destination.longitude);
  const refreshLocation = async () => {
    const coords = await getCurrentCoords();
    setCurrentLocation(coords);
    await sendLiveLocation('worker', true, job.id).catch(() => null);
  };

  const handleAction = async () => {
    if (!action?.api) return;

    if (action.requiresPhotos) {
      navigation.navigate('WorkerExecutionScreen', { job, action });
      return;
    }

    const proceed = async () => {
      setActing(true);
      try {
        let payload: any = {};
        if (action.requiresGpsCheck || action.key === 'start_travel') {
          payload = await getCurrentCoords();
          setCurrentLocation(payload);
        }
        if (action.requiresCashCollection) {
          payload = { amount: job.payable_amount || job.total_amount || job.final_price, payment_mode: 'cash', payment_status: 'paid' };
        }
        await postWorkerAction(action.api as string, payload);
        await sendLiveLocation('worker', true, job.id).catch(() => null);
        Alert.alert('Success', 'Job updated.');
        fetchDetail();
      } catch (error: any) {
        Alert.alert('Error', error.response?.data?.message || error.message || 'Action failed.');
      } finally {
        setActing(false);
      }
    };

    if (action.requiresCashCollection) {
      Alert.alert('Confirm Cash', `Have you collected Rs ${job.payable_amount || job.total_amount || job.final_price || 0} cash from customer?`, [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Yes, Complete', onPress: proceed },
      ]);
    } else {
      proceed();
    }
  };

  return (
    <ScrollView
      style={styles.container}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); fetchDetail(); }} />}
    >
      {!isOnline && <Text style={styles.offline}>Offline mode. Failed photo uploads will retry when network returns.</Text>}
      {isTracking && (
        <View style={styles.trackingBanner}>
          <Text style={styles.trackingBannerText}>📍 Live location sharing active</Text>
        </View>
      )}
      <View style={styles.card}>
        <View style={styles.row}>
          <Text style={styles.title}>#{job.booking_number || job.booking_no || job.id}</Text>
          <StatusBadge status={job.status || 'unknown'} />
        </View>
        <Text style={styles.line}>Customer: {customer.name || job.customer_name || 'N/A'}</Text>
        <Text style={styles.line}>Phone: {phone || 'N/A'}</Text>
        <Text style={styles.line}>Service: {job.service_name || job.service?.name || 'N/A'}</Text>
        <Text style={styles.line}>Vehicle: {job.vehicle_name || job.vehicle?.name || job.vehicle?.registration_number || 'N/A'}</Text>
        <Text style={styles.line}>Slot: {job.booking_date || ''} {job.slot_time || ''}</Text>
        <Text style={styles.line}>Address: {job.address || job.pickup_address || 'N/A'}</Text>
        <Text style={styles.line}>Payment: {job.payment_method || 'N/A'} | Rs {job.payable_amount || job.total_amount || job.final_price || 0}</Text>
      </View>

      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Route</Text>
        <RouteMap currentLocation={currentLocation} destination={destinationLocation ? { ...destinationLocation, title: destination.label } : undefined} />
        <MapActionButtons latitude={destinationLocation?.latitude} longitude={destinationLocation?.longitude} label={destination.label} phone={phone} onRefresh={refreshLocation} />
      </View>
      <AppButton title="WhatsApp Customer" onPress={() => openWhatsApp(phone, `I am assigned to your WheelWash booking #${job.booking_number || job.id}.`)} type="secondary" />

      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Status Timeline</Text>
        {(job.status_logs || []).map((log: any) => (
          <Text key={log.id || `${log.new_status}-${log.created_at}`} style={styles.line}>{log.new_status || log.status} - {log.created_at || ''}</Text>
        ))}
        {!job.status_logs?.length && <Text style={styles.muted}>No timeline entries yet.</Text>}
      </View>

      {action ? (
        <AppButton title={action.label} onPress={handleAction} loading={acting} style={styles.primary} />
      ) : (
        <View style={styles.waiting}><Text style={styles.waitingText}>No worker action is available for this status.</Text></View>
      )}
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  card: { backgroundColor: '#FFFFFF', padding: 16, borderRadius: 8, marginBottom: 14, borderWidth: 1, borderColor: '#E5E7EB' },
  row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
  title: { fontSize: 19, fontWeight: '800', color: '#111827' },
  line: { fontSize: 14, color: '#475569', marginBottom: 6 },
  muted: { fontSize: 14, color: '#94A3B8' },
  sectionTitle: { fontSize: 16, fontWeight: '800', marginBottom: 10, color: '#111827' },
  primary: { marginTop: 8, marginBottom: 32 },
  offline: { padding: 10, borderRadius: 8, backgroundColor: '#FEF3C7', color: '#92400E', marginBottom: 12 },
  waiting: { padding: 16, borderRadius: 8, backgroundColor: '#EEF2FF', marginBottom: 30 },
  waitingText: { color: '#3730A3', fontWeight: '700' },
  trackingBanner: { backgroundColor: '#10B981', padding: 12, borderRadius: 8, marginBottom: 14, alignItems: 'center' },
  trackingBannerText: { color: '#FFFFFF', fontWeight: '700', fontSize: 14 },
});
