import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ViewStyle } from 'react-native';
import { Brand, Spacing, Radius, Typography, Shadow } from '@/constants/theme';

interface ServiceCardProps {
  service: {
    id: string;
    name: string;
    tagline: string;
    price: number;
    duration: number;
    icon: string;
    color: string[];
    popular?: boolean;
  };
  onPress?: () => void;
  style?: ViewStyle;
  compact?: boolean;
}

export function ServiceCard({ service, onPress, style, compact = false }: ServiceCardProps) {
  return (
    <TouchableOpacity
      activeOpacity={0.88}
      onPress={onPress}
      style={[styles.card, Shadow.card, style]}
    >
      {/* Colored top accent strip */}
      <View style={[styles.strip, { backgroundColor: service.color[0] }]} />

      <View style={styles.body}>
        {/* Header row */}
        <View style={styles.headerRow}>
          <View style={[styles.iconBubble, { backgroundColor: service.color[0] + '18' }]}>
            <Text style={styles.iconText}>{service.icon}</Text>
          </View>
          {service.popular && (
            <View style={styles.popularBadge}>
              <Text style={styles.popularText}>POPULAR</Text>
            </View>
          )}
        </View>

        <Text style={styles.name}>{service.name}</Text>
        <Text style={styles.tagline}>{service.tagline}</Text>

        <View style={styles.footer}>
          <View>
            <Text style={[styles.price, { color: service.color[0] }]}>₹{service.price}</Text>
            <Text style={styles.duration}>⏱ {service.duration} min</Text>
          </View>
          <TouchableOpacity style={[styles.bookBtn, { backgroundColor: service.color[0] }]} onPress={onPress} activeOpacity={0.85}>
            <Text style={styles.bookBtnText}>{compact ? 'Book' : 'Book Now'}</Text>
          </TouchableOpacity>
        </View>
      </View>
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: Brand.white,
    borderRadius: Radius.xl,
    overflow: 'hidden',
    flex: 1,
  },
  strip: {
    height: 4,
  },
  body: {
    padding: Spacing.base,
    gap: Spacing.xs,
  },
  headerRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: Spacing.xs,
  },
  iconBubble: {
    width: 44,
    height: 44,
    borderRadius: Radius.md,
    alignItems: 'center',
    justifyContent: 'center',
  },
  iconText: {
    fontSize: 22,
  },
  popularBadge: {
    backgroundColor: Brand.warning + '20',
    paddingHorizontal: Spacing.sm,
    paddingVertical: 3,
    borderRadius: Radius.round,
  },
  popularText: {
    ...Typography.caption,
    color: Brand.warning,
    fontWeight: '700',
  },
  name: {
    ...Typography.h3,
    color: Brand.textPrimary,
  },
  tagline: {
    ...Typography.small,
    color: Brand.textSecondary,
    marginBottom: Spacing.sm,
  },
  footer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-end',
    marginTop: Spacing.xs,
  },
  price: {
    ...Typography.price,
  },
  duration: {
    ...Typography.caption,
    color: Brand.textMuted,
    marginTop: 2,
  },
  bookBtn: {
    paddingHorizontal: Spacing.base,
    paddingVertical: Spacing.sm,
    borderRadius: Radius.round,
  },
  bookBtnText: {
    ...Typography.smallMed,
    color: Brand.white,
    fontWeight: '700',
  },
});
