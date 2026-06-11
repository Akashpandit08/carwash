import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ViewStyle } from 'react-native';
import { Brand, Spacing, Radius, Typography, Shadow } from '@/constants/theme';

type BookingStatus = 'upcoming' | 'completed' | 'cancelled' | 'inprogress';

interface BookingCardProps {
  booking: {
    id: string;
    service: string;
    price: number;
    date: string;
    vehicle: string;
    status: string;
    washer?: string | null;
    washerRating?: number | null;
    rating?: number | null;
  };
  onPress?: () => void;
  style?: ViewStyle;
}

const STATUS_CONFIG: Record<string, { label: string; bg: string; text: string; dot: string }> = {
  upcoming: { label: 'Upcoming', bg: Brand.royalBlue + '15', text: Brand.royalBlue, dot: Brand.royalBlue },
  completed: { label: 'Completed', bg: Brand.success + '15', text: Brand.success, dot: Brand.success },
  cancelled: { label: 'Cancelled', bg: Brand.error + '15', text: Brand.error, dot: Brand.error },
  inprogress: { label: 'In Progress', bg: Brand.aqua + '20', text: Brand.aqua, dot: Brand.aqua },
};

function StarRating({ rating }: { rating: number }) {
  return (
    <View style={styles.stars}>
      {[1, 2, 3, 4, 5].map((s) => (
        <Text key={s} style={{ fontSize: 12, color: s <= rating ? Brand.warning : Brand.border }}>★</Text>
      ))}
    </View>
  );
}

export function BookingCard({ booking, onPress, style }: BookingCardProps) {
  const status = STATUS_CONFIG[booking.status] ?? STATUS_CONFIG.upcoming;

  return (
    <TouchableOpacity activeOpacity={0.88} onPress={onPress} style={[styles.card, Shadow.subtle, style]}>
      {/* Left accent bar */}
      <View style={[styles.accentBar, { backgroundColor: status.dot }]} />

      <View style={styles.content}>
        {/* Top row */}
        <View style={styles.topRow}>
          <View style={styles.serviceWrap}>
            <Text style={styles.serviceText}>{booking.service}</Text>
            <Text style={styles.idText}>#{booking.id}</Text>
          </View>
          <View style={[styles.statusBadge, { backgroundColor: status.bg }]}>
            <View style={[styles.statusDot, { backgroundColor: status.dot }]} />
            <Text style={[styles.statusText, { color: status.text }]}>{status.label}</Text>
          </View>
        </View>

        {/* Date & vehicle */}
        <View style={styles.metaRow}>
          <Text style={styles.metaIcon}>📅</Text>
          <Text style={styles.metaText}>{booking.date}</Text>
        </View>
        <View style={styles.metaRow}>
          <Text style={styles.metaIcon}>🚗</Text>
          <Text style={styles.metaText}>{booking.vehicle}</Text>
        </View>

        {/* Washer row */}
        {booking.washer && (
          <View style={styles.metaRow}>
            <Text style={styles.metaIcon}>👨‍🔧</Text>
            <Text style={styles.metaText}>{booking.washer}</Text>
            {booking.washerRating && (
              <View style={styles.ratingPill}>
                <Text style={styles.ratingText}>⭐ {booking.washerRating}</Text>
              </View>
            )}
          </View>
        )}

        {/* Bottom row */}
        <View style={styles.bottomRow}>
          <Text style={styles.price}>₹{booking.price}</Text>
          {booking.rating != null ? (
            <StarRating rating={booking.rating} />
          ) : booking.status === 'upcoming' ? (
            <TouchableOpacity style={styles.actionBtn} onPress={onPress}>
              <Text style={styles.actionBtnText}>Track</Text>
            </TouchableOpacity>
          ) : null}
        </View>
      </View>
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: Brand.white,
    borderRadius: Radius.xl,
    flexDirection: 'row',
    overflow: 'hidden',
    marginBottom: Spacing.md,
  },
  accentBar: {
    width: 4,
  },
  content: {
    flex: 1,
    padding: Spacing.base,
    gap: Spacing.xs,
  },
  topRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: Spacing.xs,
  },
  serviceWrap: { gap: 2 },
  serviceText: { ...Typography.h3, color: Brand.textPrimary },
  idText: { ...Typography.caption, color: Brand.textMuted },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: Radius.round,
  },
  statusDot: { width: 6, height: 6, borderRadius: 3 },
  statusText: { ...Typography.caption, fontWeight: '600' },
  metaRow: { flexDirection: 'row', alignItems: 'center', gap: Spacing.xs },
  metaIcon: { fontSize: 13 },
  metaText: { ...Typography.small, color: Brand.textSecondary, flex: 1 },
  ratingPill: {
    backgroundColor: Brand.warning + '18',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: Radius.round,
  },
  ratingText: { ...Typography.caption, color: Brand.warning, fontWeight: '700' },
  bottomRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: Spacing.sm,
    paddingTop: Spacing.sm,
    borderTopWidth: 1,
    borderTopColor: Brand.borderLight,
  },
  price: { ...Typography.h2, color: Brand.royalBlue, fontWeight: '800' },
  stars: { flexDirection: 'row', gap: 2 },
  actionBtn: {
    backgroundColor: Brand.royalBlue,
    paddingHorizontal: Spacing.base,
    paddingVertical: Spacing.xs,
    borderRadius: Radius.round,
  },
  actionBtnText: { ...Typography.smallMed, color: Brand.white, fontWeight: '700' },
});
