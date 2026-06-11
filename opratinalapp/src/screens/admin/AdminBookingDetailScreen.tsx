import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, Alert } from 'react-native';
import { getBookingDetail } from '../../api/adminApi';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { StatusBadge } from '../../components/StatusBadge';
import { AppButton } from '../../components/AppButton';

export const AdminBookingDetailScreen = ({ route, navigation }: any) => {
  const { bookingId } = route.params;
  const [booking, setBooking] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  const fetchDetail = async () => {
    try {
      const res = await getBookingDetail(bookingId);
      setBooking(res.data?.data || res.data);
    } catch (e) {
      console.log('Detail error', e);
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
  if (!booking) return <EmptyState title="Booking Not Found" />;

  return (
    <ScrollView style={styles.container}>
      <View style={styles.card}>
        <View style={styles.row}>
          <Text style={styles.title}>#{booking.booking_no || booking.id}</Text>
          <StatusBadge status={booking.status || 'unknown'} />
        </View>
        <Text style={styles.label}>Customer: <Text style={styles.value}>{booking.customer_name || booking.customer?.name || 'N/A'}</Text></Text>
        <Text style={styles.label}>Service: <Text style={styles.value}>{booking.service_name || booking.service?.name || 'N/A'}</Text></Text>
        <Text style={styles.label}>Vehicle: <Text style={styles.value}>{booking.vehicle_name || booking.vehicle?.name || 'N/A'}</Text></Text>
        <Text style={styles.label}>Address: <Text style={styles.value}>{booking.pickup_address || booking.address || 'N/A'}</Text></Text>
      </View>

      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Assignments</Text>
        <Text style={styles.label}>Pickup Driver: <Text style={styles.value}>{booking.pickup_driver?.name || 'Unassigned'}</Text></Text>
        <Text style={styles.label}>Partner: <Text style={styles.value}>{booking.partner?.name || 'Unassigned'}</Text></Text>
        <Text style={styles.label}>Worker: <Text style={styles.value}>{booking.worker?.name || 'Unassigned'}</Text></Text>

        <AppButton 
          title="Manage Assignments" 
          onPress={() => navigation.navigate('AssignTeamScreen', { booking })} 
          style={{ marginTop: 16 }}
        />
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
