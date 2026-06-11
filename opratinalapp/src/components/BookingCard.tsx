import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { StatusBadge } from './StatusBadge';

interface BookingCardProps {
  booking: any;
  onPress?: () => void;
}

export const BookingCard: React.FC<BookingCardProps> = ({ booking, onPress }) => {
  return (
    <TouchableOpacity style={styles.card} onPress={onPress} disabled={!onPress}>
      <View style={styles.header}>
        <Text style={styles.bookingNo}>#{booking?.booking_no || booking?.id || 'N/A'}</Text>
        <StatusBadge status={booking?.status || 'unknown'} />
      </View>
      <View style={styles.details}>
        <Text style={styles.serviceText}>{booking?.service_name || booking?.service?.name || 'Service Unspecified'}</Text>
        <Text style={styles.detailText}>Customer: {booking?.customer_name || booking?.customer?.name || 'Unknown'}</Text>
        <Text style={styles.detailText}>Vehicle: {booking?.vehicle_name || booking?.vehicle?.name || 'Unknown'}</Text>
        <Text style={styles.detailText}>Address: {booking?.pickup_address || booking?.address || 'N/A'}</Text>
      </View>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#FFF',
    padding: 16,
    borderRadius: 8,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#EFEFEF',
    shadowColor: '#000',
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  bookingNo: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  details: {
    marginTop: 4,
  },
  serviceText: {
    fontSize: 15,
    fontWeight: '600',
    marginBottom: 4,
    color: '#007BFF',
  },
  detailText: {
    fontSize: 14,
    color: '#555',
    marginBottom: 2,
  },
});
