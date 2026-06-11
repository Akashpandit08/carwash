import React, { useCallback, useState } from 'react';
import { ActivityIndicator, ScrollView, StatusBar, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useFocusEffect, useRouter } from 'expo-router';
import { Brand, Radius, Shadow, Spacing, Typography } from '@/constants/theme';
import { BookingDto } from '@/api/bookingApi';
import { useBookingStore } from '@/store/bookingStore';

const TABS = ['Upcoming', 'Completed', 'Cancelled'];

function tabFor(booking: BookingDto) {
  const status = String(booking.status || '').toLowerCase();
  if (status === 'completed') return 'Completed';
  if (status === 'cancelled') return 'Cancelled';
  return 'Upcoming';
}

export default function BookingsScreen() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState('Upcoming');
  const { bookings, loadBookings, loading, error } = useBookingStore();
  const filteredBookings = bookings.filter((booking) => tabFor(booking) === activeTab);

  useFocusEffect(useCallback(() => { loadBookings(); }, [loadBookings]));

  return (
    <View style={styles.root}>
      <StatusBar barStyle="dark-content" backgroundColor={Brand.white} />
      <SafeAreaView edges={['top']} style={{ backgroundColor: Brand.white }}>
        <View style={styles.header}><Text style={styles.headerTitle}>My Bookings</Text></View>
        <View style={styles.tabsWrap}>
          {TABS.map((tab) => (
            <TouchableOpacity key={tab} style={[styles.tabBtn, activeTab === tab && styles.tabBtnActive]} onPress={() => setActiveTab(tab)}>
              <Text style={[styles.tabText, activeTab === tab && styles.tabTextActive]}>{tab}</Text>
            </TouchableOpacity>
          ))}
        </View>
      </SafeAreaView>

      <ScrollView style={styles.scroll} showsVerticalScrollIndicator={false} contentContainerStyle={styles.scrollContent}>
        {loading && <View style={styles.emptyState}><ActivityIndicator color={Brand.royalBlue} /><Text style={styles.emptySub}>Loading bookings...</Text></View>}
        {error && <TouchableOpacity style={styles.errorBox} onPress={loadBookings}><Text style={styles.errorText}>{error}</Text><Text style={styles.retryText}>Retry</Text></TouchableOpacity>}

        {!loading && filteredBookings.length > 0 ? (
          <View style={styles.listContainer}>
            {filteredBookings.map((booking) => (
              <View key={String(booking.id)} style={styles.bookingCard}>
                <View style={styles.cardTop}>
                  <View style={styles.iconWrap}><Text style={styles.serviceIcon}>Wash</Text></View>
                  <View style={styles.cardInfo}>
                    <Text style={styles.serviceName}>{booking.service?.name || booking.service?.title || 'Booking'}</Text>
                    <View style={styles.metaRow}><Text style={styles.metaText}>{booking.booking_date || ''} {booking.booking_time || ''}</Text></View>
                    <View style={styles.metaRow}><Text style={styles.metaText} numberOfLines={1}>{booking.address || booking.service_mode || ''}</Text></View>
                  </View>
                </View>
                <View style={styles.cardDivider} />
                <View style={styles.cardBottom}>
                  <View style={styles.statusBadge}><View style={styles.statusDot} /><Text style={styles.statusText}>{booking.status || 'pending'}</Text></View>
                  <TouchableOpacity style={styles.detailsBtn} onPress={() => router.push({ pathname: '/booking-detail', params: { id: String(booking.id) } })}>
                    <Text style={styles.detailsBtnText}>View Details</Text>
                  </TouchableOpacity>
                </View>
              </View>
            ))}
          </View>
        ) : !loading && (
          <View style={styles.emptyState}>
            <Text style={styles.emptyTitle}>No {activeTab.toLowerCase()} bookings</Text>
            <Text style={styles.emptySub}>You do not have any bookings in this section yet.</Text>
            <TouchableOpacity style={styles.emptyBtn} onPress={() => router.push('/services')}><Text style={styles.emptyBtnText}>Book a Wash</Text></TouchableOpacity>
          </View>
        )}
        <View style={{ height: Spacing.xl }} />
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: Brand.offWhite },
  header: { paddingHorizontal: Spacing.xl, paddingVertical: Spacing.md, backgroundColor: Brand.white },
  headerTitle: { ...Typography.h1, color: Brand.textPrimary },
  tabsWrap: { flexDirection: 'row', paddingHorizontal: Spacing.xl, paddingBottom: Spacing.sm, backgroundColor: Brand.white, borderBottomWidth: 1, borderBottomColor: Brand.borderLight, gap: Spacing.lg },
  tabBtn: { paddingVertical: Spacing.sm, borderBottomWidth: 2, borderBottomColor: 'transparent' },
  tabBtnActive: { borderBottomColor: Brand.royalBlue },
  tabText: { ...Typography.bodyMed, color: Brand.textSecondary },
  tabTextActive: { color: Brand.royalBlue, fontWeight: '700' },
  scroll: { flex: 1 },
  scrollContent: { paddingHorizontal: Spacing.xl, paddingTop: Spacing.xl },
  listContainer: { gap: Spacing.lg },
  bookingCard: { backgroundColor: Brand.white, borderRadius: Radius.xl, padding: Spacing.lg, ...Shadow.subtle },
  cardTop: { flexDirection: 'row', alignItems: 'center', gap: Spacing.md },
  iconWrap: { width: 56, height: 56, borderRadius: Radius.lg, backgroundColor: Brand.surface, alignItems: 'center', justifyContent: 'center' },
  serviceIcon: { ...Typography.caption, color: Brand.royalBlue, fontWeight: '800' },
  cardInfo: { flex: 1, gap: 4 },
  serviceName: { ...Typography.h3, color: Brand.textPrimary, marginBottom: 2 },
  metaRow: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  metaText: { ...Typography.small, color: Brand.textSecondary, flex: 1 },
  cardDivider: { height: 1, backgroundColor: Brand.borderLight, marginVertical: Spacing.md },
  cardBottom: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  statusBadge: { flexDirection: 'row', alignItems: 'center', gap: 6, paddingHorizontal: 10, paddingVertical: 4, borderRadius: Radius.round, backgroundColor: Brand.royalBlue + '15' },
  statusDot: { width: 6, height: 6, borderRadius: 3, backgroundColor: Brand.royalBlue },
  statusText: { ...Typography.caption, fontWeight: '800', color: Brand.royalBlue },
  detailsBtn: { paddingHorizontal: Spacing.md, paddingVertical: Spacing.xs, borderRadius: Radius.round, borderWidth: 1, borderColor: Brand.border },
  detailsBtnText: { ...Typography.smallMed, color: Brand.royalBlue, fontWeight: '700' },
  emptyState: { alignItems: 'center', justifyContent: 'center', paddingTop: Spacing.xxxl, paddingHorizontal: Spacing.xl },
  emptyTitle: { ...Typography.h3, color: Brand.textPrimary, marginBottom: Spacing.xs },
  emptySub: { ...Typography.body, color: Brand.textSecondary, textAlign: 'center', marginBottom: Spacing.xl },
  emptyBtn: { backgroundColor: Brand.royalBlue, paddingHorizontal: Spacing.xl, paddingVertical: Spacing.md, borderRadius: Radius.round },
  emptyBtnText: { ...Typography.smallMed, color: Brand.white, fontWeight: '700' },
  errorBox: { backgroundColor: '#FFF5F5', borderColor: '#FFD0D0', borderWidth: 1, padding: Spacing.md, borderRadius: Radius.lg, marginBottom: Spacing.md },
  errorText: { color: Brand.error, fontWeight: '700' },
  retryText: { color: Brand.royalBlue, fontWeight: '800', marginTop: 4 },
});
