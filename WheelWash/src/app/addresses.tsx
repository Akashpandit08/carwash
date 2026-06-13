import React, { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, StatusBar } from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { Brand, Spacing, Radius, Typography, Shadow } from '@/constants/theme';

const SAVED_ADDRESSES = [
  {
    id: 'A1',
    type: 'Home',
    icon: '🏠',
    address: '12, Fatehabad Road, Tajganj, Dayal Bagh',
    city: 'Agra',
    state: 'Uttar Pradesh',
    pincode: '282001',
  },
  {
    id: 'A2',
    type: 'Office',
    icon: '🏢',
    address: 'Block C, Sanjay Place, Commercial Complex',
    city: 'Agra',
    state: 'Uttar Pradesh',
    pincode: '282002',
  },
];

export default function AddressesScreen() {
  const router = useRouter();
  const insets = useSafeAreaInsets();
  const [selectedAddress, setSelectedAddress] = useState('A1');

  return (
    <View style={styles.root}>
      <StatusBar barStyle="dark-content" backgroundColor={Brand.white} />
      
      {/* ── Top Bar ── */}
      <SafeAreaView edges={['top']} style={{ backgroundColor: Brand.white }}>
        <View style={styles.topBar}>
          <View style={styles.topBarLeft}>
            <TouchableOpacity onPress={() => router.canGoBack() ? router.back() : router.replace('/')} style={styles.backBtn}>
              <Text style={styles.backIcon}>←</Text>
            </TouchableOpacity>
            <Text style={styles.headerTitle}>Select Address</Text>
          </View>
          <TouchableOpacity style={styles.addBtnSmall} onPress={() => router.push('/add-address')}>
            <Text style={styles.addBtnSmallText}>+ Add New</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>

      <ScrollView style={styles.scroll} showsVerticalScrollIndicator={false} bounces={false}>
        
        <View style={styles.listContainer}>
          {SAVED_ADDRESSES.map((addr) => {
            const isSelected = selectedAddress === addr.id;
            return (
              <TouchableOpacity
                key={addr.id}
                style={[styles.addressCard, isSelected && styles.addressCardSelected]}
                onPress={() => setSelectedAddress(addr.id)}
                activeOpacity={0.9}
              >
                <View style={styles.cardHeader}>
                  <View style={styles.iconWrap}>
                    <Text style={styles.addressIcon}>{addr.icon}</Text>
                  </View>
                  <View style={styles.cardInfo}>
                    <View style={styles.titleRow}>
                      <Text style={styles.addressType}>{addr.type}</Text>
                      <TouchableOpacity style={styles.editBtn}>
                        <Text style={styles.editIcon}>✏️</Text>
                      </TouchableOpacity>
                    </View>
                    <Text style={styles.addressText}>{addr.address}</Text>
                    <Text style={styles.addressSub}>{addr.city}, {addr.state} {addr.pincode}</Text>
                  </View>
                </View>

                {/* Custom Radio Button */}
                <View style={styles.radioRow}>
                  <View style={[styles.radioOuter, isSelected && styles.radioOuterSelected]}>
                    {isSelected && <View style={styles.radioInner} />}
                  </View>
                  <Text style={[styles.radioLabel, isSelected && styles.radioLabelSelected]}>
                    {isSelected ? 'Deliver to this address' : 'Select this address'}
                  </Text>
                </View>
              </TouchableOpacity>
            );
          })}
        </View>

        {/* ── Empty State / Add More ── */}
        <View style={styles.emptyState}>
          <Text style={styles.emptyEmoji}>🗺️📍</Text>
          <Text style={styles.emptyTitle}>Wash at a different location?</Text>
          <Text style={styles.emptySub}>Add a new address for your upcoming wash.</Text>
          <TouchableOpacity style={styles.addNewBtn} onPress={() => router.push('/add-address')} activeOpacity={0.8}>
            <Text style={styles.addNewBtnText}>+ Add New Address</Text>
          </TouchableOpacity>
        </View>

        <View style={{ height: 100 }} />
      </ScrollView>

      {/* ── Sticky Bottom Button ── */}
      <View style={[styles.bottomSticky, { paddingBottom: Math.max(insets.bottom, Spacing.lg) }]}>
        <TouchableOpacity style={styles.primaryBtn} onPress={() => router.push('/coupons')} activeOpacity={0.8}>
          <Text style={styles.primaryBtnText}>Continue</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  root: {
    flex: 1,
    backgroundColor: Brand.offWhite,
  },
  topBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.sm,
    backgroundColor: Brand.white,
    borderBottomWidth: 1,
    borderBottomColor: Brand.borderLight,
  },
  topBarLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.sm,
  },
  backBtn: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: Radius.round,
    backgroundColor: Brand.offWhite,
  },
  backIcon: {
    fontSize: 18,
    color: Brand.textPrimary,
  },
  headerTitle: {
    ...Typography.h2,
    color: Brand.textPrimary,
  },
  addBtnSmall: {
    backgroundColor: Brand.aqua,
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.sm,
    borderRadius: Radius.round,
  },
  addBtnSmallText: {
    ...Typography.smallMed,
    color: Brand.white,
    fontWeight: '700',
  },
  scroll: {
    flex: 1,
  },
  listContainer: {
    padding: Spacing.xl,
    gap: Spacing.lg,
  },
  addressCard: {
    backgroundColor: Brand.white,
    borderRadius: Radius.xl,
    borderWidth: 2,
    borderColor: 'transparent',
    padding: Spacing.lg,
    ...Shadow.subtle,
  },
  addressCardSelected: {
    borderColor: Brand.royalBlue,
    backgroundColor: Brand.surface,
  },
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: Spacing.md,
    marginBottom: Spacing.lg,
  },
  iconWrap: {
    width: 40,
    height: 40,
    borderRadius: Radius.md,
    backgroundColor: Brand.offWhite,
    alignItems: 'center',
    justifyContent: 'center',
  },
  addressIcon: {
    fontSize: 20,
  },
  cardInfo: {
    flex: 1,
  },
  titleRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: Spacing.xs,
  },
  addressType: {
    ...Typography.h3,
    color: Brand.textPrimary,
  },
  editBtn: {
    padding: 4,
  },
  editIcon: {
    fontSize: 16,
  },
  addressText: {
    ...Typography.body,
    color: Brand.textSecondary,
    marginBottom: 2,
  },
  addressSub: {
    ...Typography.small,
    color: Brand.textMuted,
  },
  radioRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.sm,
    paddingTop: Spacing.md,
    borderTopWidth: 1,
    borderTopColor: Brand.borderLight,
  },
  radioOuter: {
    width: 20,
    height: 20,
    borderRadius: 10,
    borderWidth: 2,
    borderColor: Brand.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  radioOuterSelected: {
    borderColor: Brand.royalBlue,
  },
  radioInner: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: Brand.royalBlue,
  },
  radioLabel: {
    ...Typography.smallMed,
    color: Brand.textSecondary,
  },
  radioLabelSelected: {
    color: Brand.royalBlue,
    fontWeight: '700',
  },
  emptyState: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: Spacing.xxl,
    marginTop: Spacing.xl,
  },
  emptyEmoji: {
    fontSize: 64,
    marginBottom: Spacing.md,
  },
  emptyTitle: {
    ...Typography.h3,
    color: Brand.textPrimary,
    marginBottom: Spacing.xs,
    textAlign: 'center',
  },
  emptySub: {
    ...Typography.body,
    color: Brand.textSecondary,
    textAlign: 'center',
    marginBottom: Spacing.xl,
  },
  addNewBtn: {
    paddingHorizontal: Spacing.xl,
    paddingVertical: Spacing.md,
    borderRadius: Radius.round,
    borderWidth: 1,
    borderColor: Brand.royalBlue,
    backgroundColor: Brand.white,
  },
  addNewBtnText: {
    ...Typography.smallMed,
    color: Brand.royalBlue,
    fontWeight: '700',
  },
  bottomSticky: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: Brand.white,
    paddingHorizontal: Spacing.xl,
    paddingTop: Spacing.md,
    borderTopWidth: 1,
    borderTopColor: Brand.borderLight,
    ...Shadow.strong,
  },
  primaryBtn: {
    backgroundColor: Brand.royalBlue,
    height: 56,
    borderRadius: Radius.round,
    alignItems: 'center',
    justifyContent: 'center',
  },
  primaryBtnText: {
    ...Typography.h3,
    color: Brand.white,
  },
});
