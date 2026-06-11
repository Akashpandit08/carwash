import React, { useCallback, useState } from 'react';
import { ActivityIndicator, ScrollView, StatusBar, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useFocusEffect, useRouter } from 'expo-router';
import { Brand, Radius, Shadow, Spacing, Typography } from '@/constants/theme';
import { BottomNav } from '@/components/washmate/BottomNav';
import { useServiceStore } from '@/store/serviceStore';

const FILTERS = ['All', 'Exterior', 'Interior', 'Premium'];

export default function ServicesScreen() {
  const router = useRouter();
  const [activeFilter, setActiveFilter] = useState('All');
  const { services, loadServices, selectService, loading, error } = useServiceStore();

  useFocusEffect(useCallback(() => { loadServices(); }, [loadServices]));

  const filteredServices = services.filter((service) => {
    if (activeFilter === 'All') return true;
    const haystack = `${service.name || service.title || ''} ${service.category || ''}`.toLowerCase();
    return haystack.includes(activeFilter.toLowerCase());
  });

  return (
    <View style={styles.root}>
      <StatusBar barStyle="dark-content" backgroundColor={Brand.white} />
      <SafeAreaView edges={['top']} style={{ backgroundColor: Brand.white }}>
        <View style={styles.topBar}>
          <TouchableOpacity onPress={() => router.back()} style={styles.iconBtn}><Text style={styles.iconText}>Back</Text></TouchableOpacity>
          <Text style={styles.headerTitle}>Our Services</Text>
          <View style={styles.iconBtn} />
        </View>
      </SafeAreaView>

      <ScrollView style={styles.scroll} showsVerticalScrollIndicator={false} bounces={false}>
        <View style={styles.promoWrapper}>
          <View style={styles.promoBanner}>
            <View style={styles.promoContent}>
              <Text style={styles.promoTitle}>Choose the perfect wash for your car</Text>
              <Text style={styles.promoSub}>Professional care at your doorstep</Text>
            </View>
          </View>
        </View>

        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.filterContainer}>
          {FILTERS.map((filter) => (
            <TouchableOpacity key={filter} style={[styles.filterChip, activeFilter === filter && styles.filterChipActive]} onPress={() => setActiveFilter(filter)}>
              <Text style={[styles.filterText, activeFilter === filter && styles.filterTextActive]}>{filter}</Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        {loading && <View style={styles.state}><ActivityIndicator color={Brand.royalBlue} /><Text style={styles.stateText}>Loading services...</Text></View>}
        {error && <TouchableOpacity style={styles.errorBox} onPress={loadServices}><Text style={styles.errorText}>{error}</Text><Text style={styles.retryText}>Retry</Text></TouchableOpacity>}
        {!loading && filteredServices.length === 0 && <View style={styles.state}><Text style={styles.emptyTitle}>No services found</Text><Text style={styles.stateText}>Services will appear here when backend returns them.</Text></View>}

        <View style={styles.listContainer}>
          {filteredServices.map((svc) => (
            <View key={String(svc.id)} style={styles.card}>
              <View style={styles.cardHeader}>
                <View style={styles.iconWrap}><Text style={styles.serviceIcon}>Wash</Text></View>
                <View style={styles.cardInfo}>
                  <View style={styles.titleRow}>
                    <Text style={styles.serviceName}>{svc.name || svc.title || 'Service'}</Text>
                    <View style={styles.popularBadge}><Text style={styles.popularText}>API</Text></View>
                  </View>
                  <View style={styles.metaRow}>
                    <Text style={styles.metaText}>4.8 rating</Text>
                    <Text style={styles.metaDot}>-</Text>
                    <Text style={styles.metaText}>{svc.duration_minutes || svc.duration || 45} min</Text>
                  </View>
                </View>
              </View>
              <Text style={styles.tagline}>{svc.short_description || svc.description || 'Professional car wash service.'}</Text>
              <View style={styles.cardFooter}>
                <Text style={styles.price}>Rs {svc.price || 0}</Text>
                <TouchableOpacity
                  style={styles.detailsBtn}
                  onPress={async () => {
                    await selectService(svc);
                    router.push({ pathname: '/service-detail', params: { id: String(svc.id) } });
                  }}
                >
                  <Text style={styles.detailsBtnText}>View Details</Text>
                </TouchableOpacity>
              </View>
            </View>
          ))}
        </View>
        <View style={{ height: Spacing.xl + 80 }} />
      </ScrollView>

      <View style={styles.bottomNavContainer}>
        <BottomNav active="home" />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: Brand.offWhite },
  scroll: { flex: 1 },
  topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: Spacing.md, paddingVertical: Spacing.sm, backgroundColor: Brand.white, borderBottomWidth: 1, borderBottomColor: Brand.borderLight },
  iconBtn: { minWidth: 52, height: 40, alignItems: 'center', justifyContent: 'center', borderRadius: Radius.round, backgroundColor: Brand.offWhite },
  iconText: { ...Typography.caption, color: Brand.textPrimary, fontWeight: '800' },
  headerTitle: { ...Typography.h2, color: Brand.textPrimary },
  promoWrapper: { paddingHorizontal: Spacing.xl, paddingTop: Spacing.xl, paddingBottom: Spacing.md },
  promoBanner: { backgroundColor: Brand.aqua, borderRadius: Radius.xl, padding: Spacing.lg, ...Shadow.card },
  promoContent: { gap: 4 },
  promoTitle: { ...Typography.h3, color: Brand.white, lineHeight: 22 },
  promoSub: { ...Typography.small, color: Brand.white, opacity: 0.9 },
  filterContainer: { paddingHorizontal: Spacing.xl, paddingBottom: Spacing.lg, gap: Spacing.sm },
  filterChip: { paddingHorizontal: Spacing.md, paddingVertical: Spacing.sm, borderRadius: Radius.round, backgroundColor: Brand.white, borderWidth: 1, borderColor: Brand.border },
  filterChipActive: { backgroundColor: Brand.royalBlue, borderColor: Brand.royalBlue },
  filterText: { ...Typography.smallMed, color: Brand.textSecondary },
  filterTextActive: { color: Brand.white },
  state: { padding: Spacing.xl, alignItems: 'center', gap: Spacing.sm },
  stateText: { ...Typography.body, color: Brand.textSecondary, textAlign: 'center' },
  emptyTitle: { ...Typography.h3, color: Brand.textPrimary },
  errorBox: { marginHorizontal: Spacing.xl, backgroundColor: '#FFF5F5', borderColor: '#FFD0D0', borderWidth: 1, padding: Spacing.md, borderRadius: Radius.lg, marginBottom: Spacing.md },
  errorText: { color: Brand.error, fontWeight: '700' },
  retryText: { color: Brand.royalBlue, fontWeight: '800', marginTop: 4 },
  listContainer: { paddingHorizontal: Spacing.xl, gap: Spacing.lg },
  card: { backgroundColor: Brand.white, borderRadius: Radius.xl, padding: Spacing.lg, ...Shadow.subtle },
  cardHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: Spacing.sm },
  iconWrap: { width: 48, height: 48, borderRadius: Radius.md, alignItems: 'center', justifyContent: 'center', marginRight: Spacing.md, backgroundColor: Brand.surface },
  serviceIcon: { ...Typography.caption, color: Brand.royalBlue, fontWeight: '800' },
  cardInfo: { flex: 1 },
  titleRow: { flexDirection: 'row', alignItems: 'center', gap: Spacing.sm, marginBottom: 4 },
  serviceName: { ...Typography.h3, color: Brand.textPrimary, flex: 1 },
  popularBadge: { backgroundColor: Brand.warning + '20', paddingHorizontal: 8, paddingVertical: 2, borderRadius: Radius.sm },
  popularText: { ...Typography.caption, color: Brand.warning, fontWeight: '800' },
  metaRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  metaText: { ...Typography.small, color: Brand.textSecondary },
  metaDot: { ...Typography.small, color: Brand.textMuted },
  tagline: { ...Typography.body, color: Brand.textSecondary, marginBottom: Spacing.md },
  cardFooter: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: Spacing.md, borderTopWidth: 1, borderTopColor: Brand.borderLight },
  price: { ...Typography.price, color: Brand.royalBlue },
  detailsBtn: { paddingHorizontal: Spacing.lg, paddingVertical: Spacing.sm, backgroundColor: Brand.offWhite, borderRadius: Radius.round, borderWidth: 1, borderColor: Brand.border },
  detailsBtnText: { ...Typography.smallMed, color: Brand.royalBlue, fontWeight: '700' },
  bottomNavContainer: { position: 'absolute', bottom: 0, left: 0, right: 0 },
});
