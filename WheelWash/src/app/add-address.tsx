import React, { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, TextInput, KeyboardAvoidingView, Platform, StatusBar } from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { Brand, Spacing, Radius, Typography, Shadow } from '@/constants/theme';

const ADDRESS_TYPES = [
  { id: 'Home', label: 'Home', icon: '🏠' },
  { id: 'Office', label: 'Office', icon: '🏢' },
  { id: 'Other', label: 'Other', icon: '📍' },
];

export default function AddAddressScreen() {
  const router = useRouter();
  const insets = useSafeAreaInsets();
  const [selectedType, setSelectedType] = useState('Home');

  return (
    <View style={styles.root}>
      <StatusBar barStyle="dark-content" backgroundColor={Brand.white} />
      
      {/* ── Top Bar ── */}
      <SafeAreaView edges={['top']} style={{ backgroundColor: Brand.white }}>
        <View style={styles.topBar}>
          <TouchableOpacity onPress={() => router.canGoBack() ? router.back() : router.replace('/')} style={styles.backBtn}>
            <Text style={styles.backIcon}>←</Text>
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Add Address</Text>
          <View style={{ width: 40 }} />
        </View>
      </SafeAreaView>

      <KeyboardAvoidingView 
        style={{ flex: 1 }} 
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      >
        <ScrollView style={styles.scroll} showsVerticalScrollIndicator={false} bounces={false}>
          
          {/* ── Map Preview Card ── */}
          <View style={styles.mapContainer}>
            <View style={styles.mapPreview}>
              <View style={styles.mapGrid}>
                {/* Fake Map Grid lines */}
                <View style={styles.gridLineV} />
                <View style={styles.gridLineV2} />
                <View style={styles.gridLineH} />
                <View style={styles.gridLineH2} />
              </View>
              <View style={styles.pinWrap}>
                <Text style={styles.pinIcon}>📍</Text>
                <View style={styles.pinShadow} />
              </View>
            </View>
            <TouchableOpacity style={styles.currentLocBtn} activeOpacity={0.8}>
              <Text style={styles.currentLocIcon}>🎯</Text>
              <Text style={styles.currentLocText}>Use Current Location</Text>
            </TouchableOpacity>
          </View>

          {/* ── Address Form ── */}
          <View style={styles.formSection}>
            <Text style={styles.sectionTitle}>Address Details</Text>

            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>House / Flat No.</Text>
              <TextInput style={styles.input} placeholder="e.g. Flat 4B, Taj View Apartments" placeholderTextColor={Brand.textMuted} />
            </View>

            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Area / Locality</Text>
              <TextInput style={styles.input} placeholder="e.g. Fatehabad Road, Tajganj" placeholderTextColor={Brand.textMuted} />
            </View>

            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Landmark (Optional)</Text>
              <TextInput style={styles.input} placeholder="e.g. Near TDI Mall" placeholderTextColor={Brand.textMuted} />
            </View>

            <View style={styles.rowInputs}>
              <View style={[styles.inputGroup, { flex: 1.5 }]}>
                <Text style={styles.inputLabel}>City</Text>
                <TextInput style={styles.input} placeholder="Agra" placeholderTextColor={Brand.textMuted} />
              </View>
              
              <View style={[styles.inputGroup, { flex: 1 }]}>
                <Text style={styles.inputLabel}>Pincode</Text>
                <TextInput style={styles.input} placeholder="282001" keyboardType="number-pad" placeholderTextColor={Brand.textMuted} />
              </View>
            </View>
          </View>

          {/* ── Address Type Chips ── */}
          <View style={styles.typeSection}>
            <Text style={styles.sectionTitle}>Save As</Text>
            <View style={styles.typeChipsRow}>
              {ADDRESS_TYPES.map((type) => {
                const isActive = selectedType === type.id;
                return (
                  <TouchableOpacity
                    key={type.id}
                    style={[styles.typeChip, isActive && styles.typeChipActive]}
                    onPress={() => setSelectedType(type.id)}
                    activeOpacity={0.8}
                  >
                    <Text style={styles.typeChipIcon}>{type.icon}</Text>
                    <Text style={[styles.typeChipText, isActive && styles.typeChipTextActive]}>{type.label}</Text>
                  </TouchableOpacity>
                );
              })}
            </View>
          </View>

          <View style={{ height: 120 }} />
        </ScrollView>
      </KeyboardAvoidingView>

      {/* ── Sticky Bottom Button ── */}
      <View style={[styles.bottomSticky, { paddingBottom: Math.max(insets.bottom, Spacing.lg) }]}>
        <TouchableOpacity style={styles.primaryBtn} onPress={() => router.canGoBack() ? router.back() : router.replace('/')} activeOpacity={0.8}>
          <Text style={styles.primaryBtnText}>Save Address</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  root: {
    flex: 1,
    backgroundColor: Brand.white,
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
  scroll: {
    flex: 1,
  },
  mapContainer: {
    padding: Spacing.xl,
    backgroundColor: Brand.offWhite,
  },
  mapPreview: {
    height: 160,
    backgroundColor: '#E2E8F0',
    borderRadius: Radius.xl,
    overflow: 'hidden',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: Spacing.md,
  },
  mapGrid: {
    ...StyleSheet.absoluteFill,
    opacity: 0.2,
  },
  gridLineV: { position: 'absolute', left: '30%', top: 0, bottom: 0, width: 2, backgroundColor: Brand.white },
  gridLineV2: { position: 'absolute', left: '70%', top: 0, bottom: 0, width: 4, backgroundColor: Brand.white },
  gridLineH: { position: 'absolute', top: '40%', left: 0, right: 0, height: 2, backgroundColor: Brand.white },
  gridLineH2: { position: 'absolute', top: '80%', left: 0, right: 0, height: 3, backgroundColor: Brand.white },
  pinWrap: {
    alignItems: 'center',
    marginTop: -20,
  },
  pinIcon: {
    fontSize: 40,
    zIndex: 2,
  },
  pinShadow: {
    width: 16,
    height: 6,
    borderRadius: 8,
    backgroundColor: 'rgba(0,0,0,0.3)',
    marginTop: -4,
  },
  currentLocBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: Spacing.sm,
    backgroundColor: Brand.white,
    paddingVertical: Spacing.md,
    borderRadius: Radius.lg,
    borderWidth: 1,
    borderColor: Brand.aqua,
  },
  currentLocIcon: {
    fontSize: 18,
  },
  currentLocText: {
    ...Typography.smallMed,
    color: Brand.royalBlue,
    fontWeight: '700',
  },
  formSection: {
    paddingHorizontal: Spacing.xl,
    paddingTop: Spacing.xl,
    gap: Spacing.lg,
  },
  sectionTitle: {
    ...Typography.h3,
    color: Brand.textPrimary,
    marginBottom: -Spacing.sm,
  },
  inputGroup: {
    gap: 6,
  },
  inputLabel: {
    ...Typography.smallMed,
    color: Brand.textSecondary,
  },
  input: {
    backgroundColor: Brand.offWhite,
    borderWidth: 1,
    borderColor: Brand.border,
    borderRadius: Radius.lg,
    paddingHorizontal: Spacing.md,
    height: 52,
    ...Typography.body,
    color: Brand.textPrimary,
  },
  rowInputs: {
    flexDirection: 'row',
    gap: Spacing.md,
  },
  typeSection: {
    paddingHorizontal: Spacing.xl,
    paddingTop: Spacing.xl,
  },
  typeChipsRow: {
    flexDirection: 'row',
    gap: Spacing.sm,
    marginTop: Spacing.sm,
  },
  typeChip: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: Brand.offWhite,
    paddingVertical: Spacing.sm,
    borderRadius: Radius.md,
    borderWidth: 1,
    borderColor: Brand.border,
    gap: 6,
  },
  typeChipActive: {
    backgroundColor: Brand.royalBlue,
    borderColor: Brand.royalBlue,
  },
  typeChipIcon: {
    fontSize: 16,
  },
  typeChipText: {
    ...Typography.smallMed,
    color: Brand.textSecondary,
  },
  typeChipTextActive: {
    color: Brand.white,
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
