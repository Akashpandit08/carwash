import React, { useCallback } from 'react';
import { ActivityIndicator, ScrollView, StatusBar, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { useFocusEffect, useLocalSearchParams, useRouter } from 'expo-router';
import { Brand, Radius, Shadow, Spacing, Typography } from '@/constants/theme';
import { useBookingStore } from '@/store/bookingStore';

const DOOR_TO_DOOR_STATUSES = [
  'pending',
  'confirmed',
  'worker_assigned',
  'worker_on_the_way',
  'service_started',
  'service_completed',
  'completed'
];

const PICKUP_WASH_STATUSES = [
  'pending',
  'confirmed',
  'pickup_driver_assigned',
  'driver_on_the_way',
  'car_picked_up',
  'reached_partner',
  'service_started',
  'service_completed',
  'out_for_delivery',
  'delivered',
  'completed'
];

function formatStatus(status: string) {
  return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

export default function BookingDetailScreen() {
  const router = useRouter();
  const insets = useSafeAreaInsets();
  const { id } = useLocalSearchParams<{ id?: string }>();
  const { currentBooking, loadBooking, loading, error } = useBookingStore();
  const bookingId = id || currentBooking?.id;
  
  let activeStatuses = DOOR_TO_DOOR_STATUSES;
  if (currentBooking?.wash_type === 'pickup_wash') {
      activeStatuses = PICKUP_WASH_STATUSES;
  }
  if (currentBooking?.status === 'cancelled') {
      activeStatuses = ['pending', 'cancelled'];
  }

  const currentIndex = Math.max(0, activeStatuses.indexOf(String(currentBooking?.status || 'pending')));

  useFocusEffect(useCallback(() => { if (bookingId) loadBooking(bookingId); }, [bookingId, loadBooking]));

  return (
    <View style={styles.root}>
      <StatusBar barStyle="dark-content" backgroundColor={Brand.white} />
      <SafeAreaView edges={['top']} style={{ backgroundColor: Brand.white }}>
        <View style={styles.topBar}>
          <TouchableOpacity onPress={() => router.canGoBack() ? router.back() : router.replace('/')} style={styles.backBtn}><Text style={styles.backIcon}>Back</Text></TouchableOpacity>
          <Text style={styles.headerTitle}>Booking Detail</Text>
          <TouchableOpacity style={styles.trackBtn} onPress={() => router.push({ pathname: '/track', params: { id: String(bookingId || '') } })}><Text style={styles.trackBtnText}>Track</Text></TouchableOpacity>
        </View>
      </SafeAreaView>

      <ScrollView style={styles.scroll} showsVerticalScrollIndicator={false} bounces={false}>
        {loading && <View style={styles.state}><ActivityIndicator color={Brand.royalBlue} /><Text style={styles.detailSub}>Loading booking...</Text></View>}
        {error && <TouchableOpacity style={styles.errorBox} onPress={() => bookingId && loadBooking(bookingId)}><Text style={styles.errorText}>{error}</Text><Text style={styles.retryText}>Retry</Text></TouchableOpacity>}

        {currentBooking && (
          <>
            <View style={styles.section}>
              <View style={styles.card}>
                <Text style={styles.sectionTitle}>Booking Status</Text>
                <View style={styles.timeline}>
                  {activeStatuses.map((status, index) => {
                    const completed = index < currentIndex;
                    const active = index === currentIndex;
                    return (
                      <View key={status} style={styles.timelineStep}>
                        {index < activeStatuses.length - 1 && <View style={[styles.timelineLine, completed && styles.timelineLineCompleted]} />}
                        <View style={[styles.timelineDot, completed && styles.timelineDotCompleted, active && styles.timelineDotActive]}>
                          {completed && <Text style={styles.checkIcon}>✓</Text>}
                          {active && <View style={styles.innerDot} />}
                        </View>
                        <View style={styles.timelineText}>
                          <Text style={[styles.timelineTitle, (completed || active) && { color: Brand.textPrimary }]}>{formatStatus(status)}</Text>
                          <Text style={styles.timelineSub}>{active ? 'Current status' : completed ? 'Done' : 'Pending'}</Text>
                        </View>
                      </View>
                    );
                  })}
                </View>
              </View>
            </View>

            <View style={styles.section}>
              <View style={styles.card}>
                <Text style={styles.sectionTitle}>Assigned Team</Text>
                <Text style={styles.detailTitle}>Partner: {currentBooking.partner?.name || 'Not assigned'}</Text>
                <Text style={styles.detailTitle}>Worker: {currentBooking.worker?.name || 'Not assigned'}</Text>
                <Text style={styles.detailTitle}>Driver: {currentBooking.pickup_driver?.name || 'Not assigned'}</Text>
              </View>
            </View>

            <View style={styles.section}>
              <View style={styles.card}>
                <Text style={styles.sectionTitle}>Service Details</Text>
                <Detail title={currentBooking.service?.name || currentBooking.service?.title || 'Service'} sub={`Rs ${currentBooking.total_amount || currentBooking.service?.price || 0}`} />
                <Detail title={`${currentBooking.vehicle?.brand || ''} ${currentBooking.vehicle?.model || ''}`} sub={`${currentBooking.vehicle?.registration_number || ''} ${currentBooking.vehicle?.color || ''}`} />
                <Detail title={currentBooking.service_mode || 'doorstep'} sub={currentBooking.address || ''} />
              </View>
            </View>
          </>
        )}
        <View style={{ height: 120 }} />
      </ScrollView>

      <View style={[styles.bottomSticky, { paddingBottom: Math.max(insets.bottom, Spacing.lg) }]}>
        <TouchableOpacity style={styles.supportBtn} onPress={() => router.push({ pathname: '/review', params: { bookingId: String(bookingId || '') } })} activeOpacity={0.8}>
          <Text style={styles.supportBtnText}>Rate completed booking</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

function Detail({ title, sub }: { title: string; sub: string }) {
  return <View style={styles.detailRow}><View><Text style={styles.detailTitle}>{title}</Text><Text style={styles.detailSub}>{sub}</Text></View></View>;
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: Brand.offWhite },
  topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: Spacing.md, paddingVertical: Spacing.sm, backgroundColor: Brand.white, borderBottomWidth: 1, borderBottomColor: Brand.borderLight },
  backBtn: { minWidth: 48, height: 40, alignItems: 'center', justifyContent: 'center', borderRadius: Radius.round, backgroundColor: Brand.offWhite },
  backIcon: { ...Typography.caption, color: Brand.textPrimary, fontWeight: '800' },
  headerTitle: { ...Typography.h2, color: Brand.textPrimary },
  trackBtn: { paddingHorizontal: 12, paddingVertical: 6, backgroundColor: Brand.royalBlue + '15', borderRadius: Radius.round },
  trackBtnText: { ...Typography.smallMed, color: Brand.royalBlue, fontWeight: '700' },
  scroll: { flex: 1 },
  section: { paddingHorizontal: Spacing.xl, paddingTop: Spacing.xl },
  card: { backgroundColor: Brand.white, borderRadius: Radius.xl, padding: Spacing.lg, ...Shadow.subtle },
  sectionTitle: { ...Typography.h3, color: Brand.textPrimary, marginBottom: Spacing.md },
  timeline: { paddingLeft: Spacing.sm },
  timelineStep: { flexDirection: 'row', marginBottom: Spacing.lg, position: 'relative' },
  timelineLine: { position: 'absolute', left: 11, top: 24, bottom: -Spacing.lg, width: 2, backgroundColor: Brand.border },
  timelineLineCompleted: { backgroundColor: Brand.royalBlue },
  timelineDot: { width: 24, height: 24, borderRadius: 12, backgroundColor: Brand.border, alignItems: 'center', justifyContent: 'center', zIndex: 2 },
  timelineDotCompleted: { backgroundColor: Brand.royalBlue },
  timelineDotActive: { backgroundColor: Brand.royalBlue + '30', borderWidth: 2, borderColor: Brand.royalBlue },
  checkIcon: { color: Brand.white, fontSize: 12, fontWeight: '800' },
  innerDot: { width: 10, height: 10, borderRadius: 5, backgroundColor: Brand.royalBlue },
  timelineText: { marginLeft: Spacing.md, flex: 1 },
  timelineTitle: { ...Typography.bodyMed, color: Brand.textSecondary, fontWeight: '600' },
  timelineSub: { ...Typography.small, color: Brand.textMuted, marginTop: 2 },
  detailRow: { paddingVertical: Spacing.sm, borderBottomWidth: 1, borderBottomColor: Brand.borderLight },
  detailTitle: { ...Typography.bodyMed, color: Brand.textPrimary, marginBottom: 6 },
  detailSub: { ...Typography.caption, color: Brand.textSecondary },
  bottomSticky: { position: 'absolute', bottom: 0, left: 0, right: 0, backgroundColor: Brand.white, paddingHorizontal: Spacing.xl, paddingTop: Spacing.md, borderTopWidth: 1, borderTopColor: Brand.borderLight, ...Shadow.strong },
  supportBtn: { backgroundColor: Brand.royalBlue, height: 56, borderRadius: Radius.round, alignItems: 'center', justifyContent: 'center' },
  supportBtnText: { ...Typography.h3, color: Brand.white },
  state: { padding: Spacing.xl, alignItems: 'center', gap: Spacing.sm },
  errorBox: { margin: Spacing.xl, backgroundColor: '#FFF5F5', borderColor: '#FFD0D0', borderWidth: 1, padding: Spacing.md, borderRadius: Radius.lg },
  errorText: { color: Brand.error, fontWeight: '700' },
  retryText: { color: Brand.royalBlue, fontWeight: '800', marginTop: 4 },
});
