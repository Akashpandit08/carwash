import React, { useEffect, useState } from 'react';
import { View, StyleSheet, FlatList, RefreshControl } from 'react-native';
import { getBookings } from '../../api/adminApi';
import { BookingCard } from '../../components/BookingCard';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';

export const AdminBookingsScreen = ({ navigation }: any) => {
  const [bookings, setBookings] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchBookings = async () => {
    try {
      const res = await getBookings();
      setBookings(res.data?.data || res.data || []);
    } catch (e) {
      console.log('Admin Bookings error', e);
      setBookings([]);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchBookings();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchBookings();
  };

  if (loading) return <LoadingView message="Loading Bookings..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={bookings}
        keyExtractor={(item) => item.id?.toString()}
        renderItem={({ item }) => (
          <BookingCard 
            booking={item} 
            onPress={() => navigation.navigate('AdminBookingDetailScreen', { bookingId: item.id })} 
          />
        )}
        ListEmptyComponent={<EmptyState title="No Bookings Found" message="There are no active bookings right now." />}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        contentContainerStyle={bookings.length === 0 ? { flex: 1 } : { paddingBottom: 20 }}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
});
