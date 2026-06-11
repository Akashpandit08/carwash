import React, { useEffect, useState } from 'react';
import { ScrollView, View, Text, StyleSheet, Alert } from 'react-native';
import { getDriverJobDetail, updateLocation, updateJobStatus } from '../../api/pickupDriverApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { StatusBadge } from '../../components/StatusBadge';
import { AppButton } from '../../components/AppButton';
import * as Location from 'expo-location';

export const DriverJobDetailScreen = ({ route, navigation }: any) => {
  const { bookingId } = route.params;
  const [job, setJob] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [locLoading, setLocLoading] = useState(false);

  const fetchDetail = async () => {
    try {
      const res = await getDriverJobDetail(bookingId);
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

  const handleUpdateLocation = async () => {
    setLocLoading(true);
    try {
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        Alert.alert('Permission to access location was denied');
        return;
      }

      const location = await Location.getCurrentPositionAsync({});
      await updateLocation(location.coords.latitude, location.coords.longitude);
      Alert.alert('Success', 'Live location updated');
    } catch (e) {
      Alert.alert('Error', 'Failed to update location');
    } finally {
      setLocLoading(false);
    }
  };

  if (loading) return <LoadingView message="Loading Detail..." />;
  if (!job) return <EmptyState title="Job Not Found" />;

  const renderActions = () => {
    const actions = require('../../utils/statusFlow').getDriverActions(job);
    if (!actions.length) return null;

    return (
      <View style={{ marginTop: 24 }}>
        <Text style={styles.sectionTitle}>Task Execution</Text>
        {actions.map((action: any, index: number) => {
          const isImageRequired = action.nextStatus === 'car_picked_up' || action.nextStatus === 'delivered';
          const handlePress = async () => {
            if (isImageRequired) {
              if (action.nextStatus === 'car_picked_up') {
                navigation.navigate('PickupExecutionScreen', { job, nextStatus: action.nextStatus });
              } else {
                navigation.navigate('DeliveryExecutionScreen', { job, nextStatus: action.nextStatus });
              }
            } else {
              setLoading(true);
              try {
                await updateJobStatus(job.id, action.nextStatus);
                Alert.alert('Success', `Status updated to ${action.title}`);
                fetchDetail();
              } catch (e: any) {
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
        <Text style={styles.label}>Customer: <Text style={styles.value}>{job.customer_name || job.customer?.name || 'N/A'}</Text></Text>
        <Text style={styles.label}>Phone: <Text style={styles.value}>{job.phone || job.customer?.phone || 'N/A'}</Text></Text>
        <Text style={styles.label}>Address: <Text style={styles.value}>{job.pickup_address || job.address || 'N/A'}</Text></Text>
        <Text style={styles.label}>Vehicle: <Text style={styles.value}>{job.vehicle_name || job.vehicle?.name || 'N/A'}</Text></Text>
      </View>

      <AppButton title="Update Live Location" onPress={handleUpdateLocation} loading={locLoading} type="secondary" />
      {renderActions()}
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
