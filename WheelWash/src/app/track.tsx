import React, { useCallback } from 'react';
import { ActivityIndicator, ScrollView, StatusBar, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { useFocusEffect, useLocalSearchParams, useRouter } from 'expo-router';
import { Brand, Radius, Shadow, Spacing, Typography } from '@/constants/theme';
import { useBookingStore } from '@/store/bookingStore';

export default function TrackScreen() {
  const router = useRouter();
  const insets = useSafeAreaInsets();
  const { id } = useLocalSearchParams<{ id?: string }>();
  const { currentBooking, tracking, loadBooking, loadTracking, loading, error } = useBookingStore();
  const bookingId = id || currentBooking?.id;
  const trackingData = tracking as any;
  const lat = trackingData?.latitude || trackingData?.driver_location?.latitude || trackingData?.worker_location?.latitude;
  const lng = trackingData?.longitude || trackingData?.driver_location?.longitude || trackingData?.worker_location?.longitude;

  useFocusEffect(useCallback(() => {
    if (bookingId) {
      loadBooking(bookingId);
      loadTracking(bookingId);
    }
  }, [bookingId, loadBooking, loadTracking]));

  return (
    <View style={styles.root}>
      <StatusBar barStyle="dark-content" backgroundColor="transparent" translucent />
      <View style={styles.mapBackground}>
        <View style={styles.mapGrid}><View style={styles.gridLineV} /><View style={styles.gridLineV2} /><View style={styles.gridLineH} /><View style={styles.gridLineH2} /></View>
        <View style={styles.routeLine} />
        <View style={styles.pinWrap}><Text style={styles.pinIcon}>Pin</Text><View style={styles.pinShadow} /></View>
        <View style={styles.carPinWrap}><Text style={styles.pinIcon}>Car</Text></View>
      </View>

      <SafeAreaView edges={['top']} style={[styles.topBar, { top: insets.top }]}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}><Text style={styles.backIcon}>Back</Text></TouchableOpacity>
        <Text style={styles.headerTitle}>Track Booking</Text>
        <View style={{ width: 40 }} />
      </SafeAreaView>

      <View style={styles.bottomSheet}>
        <View style={styles.partnerCard}>
          <View style={styles.partnerRow}>
            <View style={styles.partnerAvatar}><Text style={styles.avatarText}>Team</Text></View>
            <View style={styles.partnerInfo}>
              <Text style={styles.partnerName}>{currentBooking?.worker?.name || currentBooking?.pickup_driver?.name || currentBooking?.partner?.name || 'Assigned team pending'}</Text>
              <Text style={styles.partnerSub}>{currentBooking?.status || 'pending'} - {currentBooking?.service_mode || 'doorstep'}</Text>
            </View>
          </View>
        </View>

        <ScrollView style={styles.sheetScroll} showsVerticalScrollIndicator={false} bounces={false}>
          {loading && <View style={styles.state}><ActivityIndicator color={Brand.royalBlue} /><Text style={styles.miniCardSub}>Loading tracking...</Text></View>}
          {error && <TouchableOpacity style={styles.errorBox} onPress={() => bookingId && loadTracking(bookingId)}><Text style={styles.errorText}>{error}</Text><Text style={styles.retryText}>Retry</Text></TouchableOpacity>}
          <View style={styles.miniCard}>
            <Text style={styles.miniCardTitle}>{currentBooking?.service?.name || currentBooking?.service?.title || 'Booking'}</Text>
            <Text style={styles.miniCardSub}>{currentBooking?.booking_date || ''} {currentBooking?.booking_time || ''}</Text>
            <Text style={styles.miniCardSub}>Live location: {lat && lng ? `${lat}, ${lng}` : 'Not available yet'}</Text>
            <Text style={styles.miniCardSub}>Partner: {currentBooking?.partner?.name || 'Pending'}</Text>
            <Text style={styles.miniCardSub}>Worker: {currentBooking?.worker?.name || 'Pending'}</Text>
            <Text style={styles.miniCardSub}>Driver: {currentBooking?.pickup_driver?.name || 'Pending'}</Text>
          </View>
          <View style={{ height: 100 }} />
        </ScrollView>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: Brand.white },
  mapBackground: { ...StyleSheet.absoluteFill, backgroundColor: '#E2E8F0', alignItems: 'center', justifyContent: 'center' },
  mapGrid: { ...StyleSheet.absoluteFill, opacity: 0.3 },
  gridLineV: { position: 'absolute', left: '30%', top: 0, bottom: 0, width: 2, backgroundColor: Brand.white },
  gridLineV2: { position: 'absolute', left: '70%', top: 0, bottom: 0, width: 4, backgroundColor: Brand.white },
  gridLineH: { position: 'absolute', top: '40%', left: 0, right: 0, height: 2, backgroundColor: Brand.white },
  gridLineH2: { position: 'absolute', top: '80%', left: 0, right: 0, height: 3, backgroundColor: Brand.white },
  routeLine: { position: 'absolute', width: 100, height: 100, borderWidth: 4, borderColor: Brand.royalBlue, borderTopColor: 'transparent', borderRightColor: 'transparent', borderRadius: 40, top: '35%', left: '35%', opacity: 0.8 },
  pinWrap: { position: 'absolute', top: '30%', left: '30%', alignItems: 'center' },
  carPinWrap: { position: 'absolute', top: '45%', left: '55%', alignItems: 'center', backgroundColor: Brand.white, padding: 8, borderRadius: Radius.round, ...Shadow.strong },
  pinIcon: { ...Typography.caption, color: Brand.royalBlue, fontWeight: '900' },
  pinShadow: { width: 16, height: 6, borderRadius: 8, backgroundColor: 'rgba(0,0,0,0.3)', marginTop: 4 },
  topBar: { position: 'absolute', left: 0, right: 0, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: Spacing.md, paddingVertical: Spacing.sm },
  backBtn: { minWidth: 48, height: 40, alignItems: 'center', justifyContent: 'center', borderRadius: Radius.round, backgroundColor: Brand.white, ...Shadow.subtle },
  backIcon: { ...Typography.caption, color: Brand.textPrimary, fontWeight: '800' },
  headerTitle: { ...Typography.h2, color: Brand.textPrimary, textShadowColor: 'rgba(255,255,255,0.8)', textShadowOffset: { width: 0, height: 1 }, textShadowRadius: 4 },
  bottomSheet: { position: 'absolute', bottom: 0, left: 0, right: 0, backgroundColor: Brand.white, borderTopLeftRadius: 32, borderTopRightRadius: 32, height: '45%', ...Shadow.strong },
  partnerCard: { backgroundColor: Brand.white, borderRadius: Radius.xl, padding: Spacing.xl, borderBottomWidth: 1, borderBottomColor: Brand.borderLight },
  partnerRow: { flexDirection: 'row', alignItems: 'center', gap: Spacing.md },
  partnerAvatar: { width: 56, height: 56, borderRadius: Radius.round, backgroundColor: Brand.surface, alignItems: 'center', justifyContent: 'center' },
  avatarText: { ...Typography.caption, color: Brand.royalBlue, fontWeight: '800' },
  partnerInfo: { flex: 1 },
  partnerName: { ...Typography.h3, color: Brand.textPrimary },
  partnerSub: { ...Typography.smallMed, color: Brand.royalBlue, marginTop: 2 },
  sheetScroll: { flex: 1 },
  miniCard: { backgroundColor: Brand.offWhite, margin: Spacing.xl, padding: Spacing.md, borderRadius: Radius.lg, borderWidth: 1, borderColor: Brand.borderLight },
  miniCardTitle: { ...Typography.bodyMed, color: Brand.textPrimary },
  miniCardSub: { ...Typography.small, color: Brand.textSecondary, marginTop: 6 },
  state: { padding: Spacing.lg, alignItems: 'center' },
  errorBox: { marginHorizontal: Spacing.xl, marginTop: Spacing.md, backgroundColor: '#FFF5F5', borderColor: '#FFD0D0', borderWidth: 1, padding: Spacing.md, borderRadius: Radius.lg },
  errorText: { color: Brand.error, fontWeight: '700' },
  retryText: { color: Brand.royalBlue, fontWeight: '800', marginTop: 4 },
});
