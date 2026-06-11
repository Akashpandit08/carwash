import React, { useCallback } from 'react';
import { ActivityIndicator, Alert, ScrollView, StatusBar, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useFocusEffect, useRouter } from 'expo-router';
import { Brand, Radius, Shadow, Spacing, Typography } from '@/constants/theme';
import { BottomNav } from '@/components/washmate/BottomNav';
import { useVehicleStore } from '@/store/vehicleStore';

export default function VehiclesScreen() {
  const router = useRouter();
  const { vehicles, selectedVehicle, loadVehicles, selectVehicle, removeVehicle, loading, error } = useVehicleStore();

  useFocusEffect(useCallback(() => { loadVehicles(); }, [loadVehicles]));

  const onDelete = (id: string) => {
    Alert.alert('Delete vehicle?', 'This vehicle will be removed from your account.', [
      { text: 'Cancel', style: 'cancel' },
      { text: 'Delete', style: 'destructive', onPress: () => removeVehicle(id).catch((err) => Alert.alert('Delete failed', err.message)) },
    ]);
  };

  return (
    <View style={styles.root}>
      <StatusBar barStyle="dark-content" backgroundColor={Brand.white} />
      <SafeAreaView edges={['top']} style={{ backgroundColor: Brand.white }}>
        <View style={styles.topBar}>
          <Text style={styles.headerTitle}>My Vehicles</Text>
          <TouchableOpacity style={styles.addBtnSmall} onPress={() => router.push('/add-vehicle')}>
            <Text style={styles.addBtnSmallText}>+ Add</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>

      <ScrollView style={styles.scroll} showsVerticalScrollIndicator={false} contentContainerStyle={styles.scrollContent}>
        {loading && <View style={styles.state}><ActivityIndicator color={Brand.royalBlue} /><Text style={styles.stateText}>Loading vehicles...</Text></View>}
        {error && <TouchableOpacity style={styles.errorBox} onPress={loadVehicles}><Text style={styles.errorText}>{error}</Text><Text style={styles.retryText}>Retry</Text></TouchableOpacity>}
        {!loading && vehicles.length === 0 && (
          <View style={styles.state}>
            <Text style={styles.emptyTitle}>No vehicles yet</Text>
            <Text style={styles.stateText}>Add your vehicle to start booking real services.</Text>
          </View>
        )}

        <View style={styles.listContainer}>
          {vehicles.map((vehicle) => {
            const isSelected = selectedVehicle?.id === vehicle.id;
            return (
              <TouchableOpacity key={vehicle.id} style={[styles.card, isSelected && styles.cardSelected]} onPress={() => selectVehicle(vehicle)} activeOpacity={0.9}>
                <View style={styles.cardHeader}>
                  <View style={[styles.imagePlaceholder, isSelected && styles.imagePlaceholderSelected]}><Text style={styles.carEmoji}>Car</Text></View>
                  <View style={styles.cardInfo}>
                    <View style={styles.titleRow}>
                      <Text style={styles.carName}>{vehicle.brand} {vehicle.model}</Text>
                      {isSelected && <View style={styles.selectedBadge}><Text style={styles.selectedBadgeText}>SELECTED</Text></View>}
                    </View>
                    <Text style={styles.carType}>{vehicle.type}</Text>
                    <View style={styles.plateWrap}><View style={styles.plateSide} /><Text style={styles.plateText}>{vehicle.registrationNumber}</Text></View>
                  </View>
                </View>
                <View style={styles.cardActions}>
                  <TouchableOpacity style={styles.actionBtn} onPress={() => router.push('/add-vehicle')}><Text style={styles.actionText}>Edit</Text></TouchableOpacity>
                  <View style={styles.actionDivider} />
                  <TouchableOpacity style={styles.actionBtn} onPress={() => onDelete(vehicle.id)}><Text style={[styles.actionText, { color: Brand.error }]}>Delete</Text></TouchableOpacity>
                </View>
              </TouchableOpacity>
            );
          })}
        </View>

        <TouchableOpacity style={styles.addNewBtn} onPress={() => router.push('/add-vehicle')} activeOpacity={0.8}>
          <Text style={styles.addNewIcon}>+</Text>
          <Text style={styles.addNewText}>Add New Vehicle</Text>
        </TouchableOpacity>
        <View style={{ height: Spacing.xl + 80 }} />
      </ScrollView>

      <View style={styles.bottomNavContainer}>
        <BottomNav active="vehicles" />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: Brand.offWhite },
  scroll: { flex: 1 },
  scrollContent: { paddingHorizontal: Spacing.xl, paddingTop: Spacing.xl },
  topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: Spacing.xl, paddingVertical: Spacing.md, backgroundColor: Brand.white, borderBottomWidth: 1, borderBottomColor: Brand.borderLight },
  headerTitle: { ...Typography.h1, color: Brand.textPrimary },
  addBtnSmall: { backgroundColor: Brand.aqua, paddingHorizontal: Spacing.md, paddingVertical: Spacing.sm, borderRadius: Radius.round },
  addBtnSmallText: { ...Typography.smallMed, color: Brand.white, fontWeight: '700' },
  state: { alignItems: 'center', padding: Spacing.xl, gap: Spacing.sm },
  stateText: { ...Typography.body, color: Brand.textSecondary, textAlign: 'center' },
  emptyTitle: { ...Typography.h3, color: Brand.textPrimary },
  errorBox: { backgroundColor: '#FFF5F5', borderColor: '#FFD0D0', borderWidth: 1, padding: Spacing.md, borderRadius: Radius.lg, marginBottom: Spacing.md },
  errorText: { color: Brand.error, fontWeight: '700' },
  retryText: { color: Brand.royalBlue, fontWeight: '800', marginTop: 4 },
  listContainer: { gap: Spacing.lg, marginBottom: Spacing.xl },
  card: { backgroundColor: Brand.white, borderRadius: Radius.xl, borderWidth: 2, borderColor: 'transparent', ...Shadow.subtle, overflow: 'hidden' },
  cardSelected: { borderColor: Brand.royalBlue, backgroundColor: Brand.surface },
  cardHeader: { flexDirection: 'row', padding: Spacing.lg, gap: Spacing.md },
  imagePlaceholder: { width: 80, height: 80, borderRadius: Radius.lg, backgroundColor: Brand.offWhite, alignItems: 'center', justifyContent: 'center' },
  imagePlaceholderSelected: { backgroundColor: Brand.white },
  carEmoji: { ...Typography.smallMed, color: Brand.royalBlue },
  cardInfo: { flex: 1 },
  titleRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 2, gap: 8 },
  carName: { ...Typography.h2, color: Brand.textPrimary, flex: 1 },
  selectedBadge: { backgroundColor: Brand.royalBlue, paddingHorizontal: 8, paddingVertical: 4, borderRadius: Radius.sm },
  selectedBadgeText: { ...Typography.caption, color: Brand.white, fontWeight: '800' },
  carType: { ...Typography.small, color: Brand.textSecondary, marginBottom: Spacing.sm },
  plateWrap: { flexDirection: 'row', backgroundColor: Brand.white, borderWidth: 1, borderColor: Brand.border, borderRadius: Radius.sm, overflow: 'hidden', alignSelf: 'flex-start' },
  plateSide: { width: 12, backgroundColor: Brand.royalBlue },
  plateText: { ...Typography.caption, fontWeight: '700', color: Brand.textPrimary, paddingHorizontal: 8, paddingVertical: 4, letterSpacing: 1 },
  cardActions: { flexDirection: 'row', borderTopWidth: 1, borderTopColor: Brand.borderLight },
  actionBtn: { flex: 1, alignItems: 'center', justifyContent: 'center', paddingVertical: Spacing.md },
  actionDivider: { width: 1, backgroundColor: Brand.borderLight },
  actionText: { ...Typography.smallMed, color: Brand.textSecondary },
  addNewBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: Spacing.lg, borderRadius: Radius.lg, borderWidth: 2, borderColor: Brand.border, borderStyle: 'dashed', gap: Spacing.sm, backgroundColor: Brand.white },
  addNewIcon: { fontSize: 24, color: Brand.textSecondary, marginTop: -2 },
  addNewText: { ...Typography.h3, color: Brand.textSecondary },
  bottomNavContainer: { position: 'absolute', bottom: 0, left: 0, right: 0 },
});
