import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import React from 'react';
import { Platform, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { Brand, Shadow, Spacing, Typography } from '@/constants/theme';

export type TabName = 'home' | 'bookings' | 'vehicles' | 'profile';

type BottomNavProps = {
  active: TabName;
  onTab?: (tab: TabName) => void;
};

const TABS: {
  name: TabName;
  icon: keyof typeof Ionicons.glyphMap;
  activeIcon: keyof typeof Ionicons.glyphMap;
  label: string;
  href: string;
}[] = [
  { name: 'home', icon: 'home-outline', activeIcon: 'home', label: 'Home', href: '/(tabs)' },
  { name: 'bookings', icon: 'calendar-outline', activeIcon: 'calendar', label: 'Bookings', href: '/(tabs)/bookings' },
  { name: 'vehicles', icon: 'car-sport-outline', activeIcon: 'car-sport', label: 'Vehicles', href: '/vehicles' },
  { name: 'profile', icon: 'person-outline', activeIcon: 'person', label: 'Profile', href: '/(tabs)/profile' },
];

export function BottomNav({ active, onTab }: BottomNavProps) {
  const goToTab = (tab: (typeof TABS)[number]) => {
    onTab?.(tab.name);
    if (active !== tab.name) {
      router.push(tab.href as never);
    }
  };

  return (
    <View style={styles.container}>
      {TABS.map((tab) => {
        const isActive = active === tab.name;

        return (
          <TouchableOpacity
            key={tab.name}
            style={styles.tab}
            onPress={() => goToTab(tab)}
            activeOpacity={0.75}
          >
            {isActive && <View style={styles.activePill} />}
            <View style={[styles.iconWrap, isActive && styles.iconWrapActive]}>
              <Ionicons
                name={isActive ? tab.activeIcon : tab.icon}
                size={22}
                color={isActive ? Brand.royalBlue : Brand.textMuted}
              />
            </View>
            <Text style={[styles.label, isActive && styles.labelActive]}>{tab.label}</Text>
          </TouchableOpacity>
        );
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    backgroundColor: Brand.white,
    paddingTop: Spacing.sm,
    paddingBottom: Platform.OS === 'android' ? Spacing.base : Spacing.xs,
    paddingHorizontal: Spacing.sm,
    borderTopWidth: 1,
    borderTopColor: Brand.borderLight,
    ...Shadow.card,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -2 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 12,
  },
  tab: {
    flex: 1,
    alignItems: 'center',
    gap: 3,
    position: 'relative',
    paddingTop: 2,
  },
  activePill: {
    position: 'absolute',
    top: -8,
    width: 28,
    height: 3,
    backgroundColor: Brand.royalBlue,
    borderRadius: 99,
  },
  iconWrap: {
    width: 36,
    height: 36,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 12,
  },
  iconWrapActive: {
    backgroundColor: `${Brand.royalBlue}15`,
  },
  label: {
    ...Typography.caption,
    color: Brand.textMuted,
    fontWeight: '500',
  },
  labelActive: {
    color: Brand.royalBlue,
    fontWeight: '700',
  },
});
