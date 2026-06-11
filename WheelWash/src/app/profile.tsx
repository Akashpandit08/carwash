import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, StatusBar } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { Brand, Spacing, Radius, Typography, Shadow } from '@/constants/theme';
import { BottomNav } from '@/components/washmate/BottomNav';

const MENU_ITEMS = [
  { id: 'vehicles', label: 'My Vehicles', icon: '🚗', route: '/vehicles' },
  { id: 'bookings', label: 'My Bookings', icon: '📅', route: '/(tabs)/bookings' },
  { id: 'addresses', label: 'Saved Addresses', icon: '📍', route: '/addresses' },
  { id: 'coupons', label: 'Offers & Coupons', icon: '🎫', route: '/coupons' },
  { id: 'payment', label: 'Payment Methods', icon: '💳', route: '/payment' },
  { id: 'support', label: 'Help & Support', icon: '🎧', route: '/review' }, // Point to review for demo
  { id: 'settings', label: 'Settings', icon: '⚙️', route: '' },
];

export default function ProfileScreen() {
  const router = useRouter();

  return (
    <View style={styles.root}>
      <StatusBar barStyle="dark-content" backgroundColor={Brand.white} />
      
      {/* ── Header ── */}
      <SafeAreaView edges={['top']} style={{ backgroundColor: Brand.white }}>
        <View style={styles.header}>
          <Text style={styles.headerTitle}>Profile</Text>
        </View>
      </SafeAreaView>

      <ScrollView style={styles.scroll} showsVerticalScrollIndicator={false} contentContainerStyle={styles.scrollContent}>
        
        {/* ── User Card ── */}
        <View style={styles.userCard}>
          <View style={styles.avatarWrap}>
            <Text style={styles.avatarEmoji}>👨🏻‍💼</Text>
          </View>
          <View style={styles.userInfo}>
            <View style={styles.nameRow}>
              <Text style={styles.userName}>Akash Sharma</Text>
              <View style={styles.verifiedBadge}>
                <Text style={styles.verifiedIcon}>✓</Text>
              </View>
            </View>
            <Text style={styles.userPhone}>+91 98765 43210</Text>
          </View>
          <TouchableOpacity style={styles.editBtn}>
            <Text style={styles.editIcon}>✏️</Text>
          </TouchableOpacity>
        </View>

        {/* ── Menu List ── */}
        <View style={styles.menuContainer}>
          {MENU_ITEMS.map((item, index) => (
            <TouchableOpacity 
              key={item.id} 
              style={[styles.menuItem, index === MENU_ITEMS.length - 1 && { borderBottomWidth: 0 }]}
              onPress={() => item.route ? router.push(item.route as any) : null}
              activeOpacity={0.8}
            >
              <View style={styles.menuIconWrap}>
                <Text style={styles.menuIcon}>{item.icon}</Text>
              </View>
              <Text style={styles.menuLabel}>{item.label}</Text>
              <Text style={styles.menuArrow}>›</Text>
            </TouchableOpacity>
          ))}
        </View>

        {/* ── Logout Button ── */}
        <TouchableOpacity style={styles.logoutBtn} onPress={() => router.replace('/login')} activeOpacity={0.8}>
          <Text style={styles.logoutIcon}>🚪</Text>
          <Text style={styles.logoutText}>Logout</Text>
        </TouchableOpacity>

        <View style={{ height: Spacing.xl + 80 }} />
      </ScrollView>

      {/* ── Bottom Navigation ── */}
      <View style={styles.bottomNavContainer}>
        <BottomNav active="profile" />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  root: {
    flex: 1,
    backgroundColor: Brand.offWhite,
  },
  header: {
    paddingHorizontal: Spacing.xl,
    paddingVertical: Spacing.md,
    backgroundColor: Brand.white,
    borderBottomWidth: 1,
    borderBottomColor: Brand.borderLight,
  },
  headerTitle: {
    ...Typography.h1,
    color: Brand.textPrimary,
  },
  scroll: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: Spacing.xl,
    paddingTop: Spacing.xl,
  },
  userCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Brand.white,
    padding: Spacing.lg,
    borderRadius: Radius.xl,
    marginBottom: Spacing.xl,
    ...Shadow.subtle,
  },
  avatarWrap: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: Brand.surface,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: Spacing.md,
  },
  avatarEmoji: {
    fontSize: 32,
  },
  userInfo: {
    flex: 1,
  },
  nameRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.xs,
    marginBottom: 4,
  },
  userName: {
    ...Typography.h2,
    color: Brand.textPrimary,
  },
  verifiedBadge: {
    width: 16,
    height: 16,
    borderRadius: 8,
    backgroundColor: Brand.success,
    alignItems: 'center',
    justifyContent: 'center',
  },
  verifiedIcon: {
    fontSize: 10,
    color: Brand.white,
    fontWeight: '800',
  },
  userPhone: {
    ...Typography.body,
    color: Brand.textSecondary,
  },
  editBtn: {
    padding: Spacing.sm,
    backgroundColor: Brand.offWhite,
    borderRadius: Radius.round,
  },
  editIcon: {
    fontSize: 16,
  },
  menuContainer: {
    backgroundColor: Brand.white,
    borderRadius: Radius.xl,
    paddingHorizontal: Spacing.lg,
    marginBottom: Spacing.xl,
    ...Shadow.subtle,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: Spacing.lg,
    borderBottomWidth: 1,
    borderBottomColor: Brand.borderLight,
  },
  menuIconWrap: {
    width: 40,
    height: 40,
    borderRadius: Radius.md,
    backgroundColor: Brand.offWhite,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: Spacing.md,
  },
  menuIcon: {
    fontSize: 20,
  },
  menuLabel: {
    flex: 1,
    ...Typography.bodyMed,
    color: Brand.textPrimary,
  },
  menuArrow: {
    fontSize: 24,
    color: Brand.textMuted,
  },
  logoutBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: Spacing.sm,
    backgroundColor: Brand.white,
    paddingVertical: Spacing.lg,
    borderRadius: Radius.xl,
    borderWidth: 1,
    borderColor: Brand.error + '50',
    marginBottom: Spacing.xxl,
  },
  logoutIcon: {
    fontSize: 20,
  },
  logoutText: {
    ...Typography.bodyMed,
    color: Brand.error,
    fontWeight: '700',
  },
  bottomNavContainer: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
  },
});
