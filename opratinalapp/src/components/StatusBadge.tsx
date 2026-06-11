import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { BookingStatus } from '../constants/bookingStatus';

interface StatusBadgeProps {
  status: string;
}

export const StatusBadge: React.FC<StatusBadgeProps> = ({ status }) => {
  const getStatusColor = () => {
    switch (status) {
      case BookingStatus.PENDING: return { bg: '#FFF3CD', text: '#856404' };
      case BookingStatus.CONFIRMED: return { bg: '#CCE5FF', text: '#004085' };
      case BookingStatus.ASSIGNED_PICKUP_DRIVER:
      case BookingStatus.DRIVER_STARTED:
      case BookingStatus.ARRIVED_AT_CUSTOMER:
      case BookingStatus.VEHICLE_PICKED_UP: return { bg: '#D1ECF1', text: '#0C5460' };
      case BookingStatus.ASSIGNED_WORKER:
      case BookingStatus.WASH_STARTED: return { bg: '#D4EDDA', text: '#155724' };
      case BookingStatus.WASH_COMPLETED:
      case BookingStatus.READY_FOR_DELIVERY: return { bg: '#C3E6CB', text: '#155724' };
      case BookingStatus.OUT_FOR_DELIVERY: return { bg: '#D1ECF1', text: '#0C5460' };
      case BookingStatus.DELIVERED: return { bg: '#D4EDDA', text: '#155724' };
      case BookingStatus.CANCELLED: return { bg: '#F8D7DA', text: '#721C24' };
      default: return { bg: '#E2E3E5', text: '#383D41' };
    }
  };

  const colors = getStatusColor();

  return (
    <View style={[styles.badge, { backgroundColor: colors.bg }]}>
      <Text style={[styles.text, { color: colors.text }]}>{status?.replace(/_/g, ' ').toUpperCase()}</Text>
    </View>
  );
};

const styles = StyleSheet.create({
  badge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
    alignSelf: 'flex-start',
  },
  text: {
    fontSize: 10,
    fontWeight: 'bold',
  },
});
