import { Ionicons } from '@expo/vector-icons';
import { router, useFocusEffect } from 'expo-router';
import { useCallback, useState } from 'react';
import { ActivityIndicator, Image, ScrollView, StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { AppBanner, getProfile, listBanners } from '@/api/customerApi';
import { Card, ScreenHeader, SelectedBadge } from '@/components/wheelwash/ui';
import { handleNotificationRedirect } from '@/lib/navigationRedirect';
import { BORDER, MUTED, PRIMARY, SUCCESS, TEXT, serviceCategories } from '@/lib/wheelwash-data';
import { useAuthStore } from '@/store/authStore';
import { useBookingStore } from '@/store/bookingStore';
import { useLocationStore } from '@/store/locationStore';
import { useServiceStore } from '@/store/serviceStore';
import { useVehicleStore } from '@/store/vehicleStore';

export default function HomeTab() {
  const [profileName, setProfileName] = useState('');
  const [profileError, setProfileError] = useState<string | null>(null);
  const [banners, setBanners] = useState<AppBanner[]>([]);
  const { user } = useAuthStore();
  const { location, loadLocation } = useLocationStore();
  const { selectedVehicle, loadVehicles, loading: vehiclesLoading, error: vehicleError } = useVehicleStore();
  const { services, selectedService, loadServices, selectService, loading: servicesLoading, error: serviceError } = useServiceStore();
  const { bookings, loadBookings, loading: bookingsLoading, error: bookingError } = useBookingStore();
  const featured = selectedService || services[0];
  const heroBanner = banners[0];
  const heroImage = heroBanner?.image_url || heroBanner?.image;
  const upcoming = bookings.find((item) => !['completed', 'cancelled'].includes(String(item.status || '').toLowerCase()));

  const load = useCallback(async () => {
    await Promise.all([loadLocation(), loadVehicles(), loadServices(), loadBookings()]);
    try {
      const [profile, homeBanners] = await Promise.all([
        getProfile() as Promise<{ name?: string; user?: { name?: string } }>,
        listBanners('home_top'),
      ]);
      setProfileName(profile?.name || profile?.user?.name || '');
      setBanners(homeBanners);
      setProfileError(null);
    } catch (err) {
      setProfileError(err instanceof Error ? err.message : 'Profile load failed.');
    }
  }, [loadLocation, loadVehicles, loadServices, loadBookings]);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  return (
    <SafeAreaView style={styles.root} edges={['top']}>
      <ScreenHeader />
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        <Text style={styles.greeting}>Hi {profileName || user?.name || 'Customer'}</Text>
        <TouchableOpacity style={styles.locationRow} onPress={() => router.push('/location')}>
          <Ionicons name="location" size={20} color={MUTED} />
          <Text style={styles.locationText}>{location?.city || 'Select location'}{location?.region ? `, ${location.region}` : ''}</Text>
          <Ionicons name="chevron-down" size={18} color={TEXT} />
        </TouchableOpacity>

        {(profileError || vehicleError || serviceError || bookingError) && (
          <TouchableOpacity style={styles.errorBox} onPress={load}>
            <Text style={styles.errorText}>{profileError || vehicleError || serviceError || bookingError}</Text>
            <Text style={styles.retryText}>Tap to retry</Text>
          </TouchableOpacity>
        )}

        <View style={styles.search}>
          <Ionicons name="search" size={25} color={MUTED} />
          <TextInput style={styles.searchInput} placeholder="Search services" placeholderTextColor="#7B8494" />
          <View style={styles.searchDivider} />
          <Ionicons name="options-outline" size={25} color={MUTED} />
        </View>

        <TouchableOpacity
          activeOpacity={0.9}
          style={[styles.hero, { backgroundColor: heroBanner?.background_color || '#82D9F0' }]}
          onPress={async () => {
            if (heroBanner) {
              await handleBannerPress(heroBanner, selectService, services);
              return;
            }
            if (featured) await selectService(featured);
            router.push('/service-detail');
          }}
        >
          {heroImage ? (
            <Image source={{ uri: heroImage }} style={StyleSheet.absoluteFill} resizeMode="cover" />
          ) : null}
          <View style={[styles.heroOverlay, !heroImage && styles.heroOverlayEmpty]} />
          {!heroImage && (
            <View style={styles.heroArt} pointerEvents="none">
              <View style={styles.heroArtBubble}>
                <Ionicons name="car-sport" size={44} color="#0B4AA9" />
              </View>
              <View style={styles.heroArtSmallBubble}>
                <Ionicons name="water" size={24} color="#fff" />
              </View>
            </View>
          )}
          <View style={styles.heroCopy}>
            <Text style={styles.heroTitle}>{heroBanner?.title || 'Book doorstep car wash in minutes'}</Text>
            <Text style={styles.heroText}>{heroBanner?.subtitle || 'Professional care for your car, at your doorstep.'}</Text>
            <View style={styles.heroButton}>
              <Text style={styles.heroButtonText}>{heroBanner?.button_label || 'Book Now'}</Text>
              <Ionicons name="arrow-forward" size={20} color="#fff" />
            </View>
          </View>
        </TouchableOpacity>

        {vehiclesLoading ? (
          <Card style={styles.stateCard}><ActivityIndicator color={PRIMARY} /><Text style={styles.muted}>Loading vehicles...</Text></Card>
        ) : selectedVehicle ? (
          <TouchableOpacity activeOpacity={0.9} onPress={() => router.push('/vehicles')}>
            <Card style={styles.vehicleCard}>
              <View style={styles.vehicleThumb}><Ionicons name="car-sport" size={34} color={PRIMARY} /></View>
              <View style={styles.vehicleInfo}>
                <Text style={styles.cardLabel}>Your Vehicle</Text>
                <Text style={styles.vehicleName} numberOfLines={2}>{selectedVehicle.brand} {selectedVehicle.model} - {selectedVehicle.registrationNumber}</Text>
              </View>
              <View style={styles.selectedWrap}><SelectedBadge /></View>
            </Card>
          </TouchableOpacity>
        ) : (
          <TouchableOpacity activeOpacity={0.9} onPress={() => router.push('/add-vehicle')}>
            <Card style={styles.addVehicleCard}>
              <View style={styles.addIcon}><Ionicons name="car-sport-outline" size={30} color={PRIMARY} /></View>
              <View style={{ flex: 1 }}>
                <Text style={styles.vehicleName}>Add Vehicle</Text>
                <Text style={styles.muted}>Save your car details to book faster.</Text>
              </View>
              <Ionicons name="arrow-forward" size={24} color={PRIMARY} />
            </Card>
          </TouchableOpacity>
        )}

        <View style={styles.categoryGrid}>
          {serviceCategories.map((item) => (
            <TouchableOpacity key={item.title} style={styles.category} activeOpacity={0.85} onPress={() => router.push('/services')}>
              <View style={[styles.categoryIcon, { backgroundColor: item.tone }]}>
                <Ionicons name={item.icon as keyof typeof Ionicons.glyphMap} size={34} color={item.color} />
              </View>
              <Text style={styles.categoryText}>{item.title}</Text>
            </TouchableOpacity>
          ))}
        </View>

        {servicesLoading ? (
          <Card style={styles.stateCard}><ActivityIndicator color={PRIMARY} /><Text style={styles.muted}>Loading services...</Text></Card>
        ) : featured ? (
          <TouchableOpacity activeOpacity={0.9} onPress={async () => { await selectService(featured); router.push('/service-detail'); }}>
            <Card style={styles.serviceCard}>
              {featured.image_url || featured.image ? (
                <Image source={{ uri: featured.image_url || featured.image }} style={styles.serviceImage} resizeMode="cover" />
              ) : (
                <View style={[styles.serviceImage, styles.serviceImageEmpty]}><Ionicons name="image-outline" size={34} color={MUTED} /></View>
              )}
              <View style={styles.serviceBody}>
                <View style={styles.featureRow}>
                  <Text style={styles.featureBadge}>Featured</Text>
                  <Text style={styles.rating}>4.8</Text>
                </View>
                <Text style={styles.serviceTitle}>{featured.name || featured.title || 'Service'}</Text>
                <Text style={styles.serviceDesc}>{featured.short_description || featured.description || 'Professional car wash service.'}</Text>
                <View style={styles.metaRow}>
                  <Ionicons name="time-outline" size={24} color={PRIMARY} />
                  <Text style={styles.metaStrong}>{featured.duration_minutes || featured.duration || 45} min</Text>
                  <Ionicons name="water-outline" size={24} color={PRIMARY} />
                  <Text style={styles.metaStrong}>Water Efficient</Text>
                </View>
                <View style={styles.priceRow}>
                  <Text style={styles.starts}>Starts at{'\n'}<Text style={styles.price}>Rs {featured.price || 0}</Text></Text>
                  <View style={styles.bookButton}><Text style={styles.bookButtonText}>Book Now</Text></View>
                </View>
              </View>
            </Card>
          </TouchableOpacity>
        ) : (
          <Card style={styles.stateCard}><Text style={styles.vehicleName}>No services available</Text><Text style={styles.muted}>Add services in backend, then retry.</Text></Card>
        )}

        <Card style={styles.upcoming}>
          <View style={styles.upcomingHead}>
            <Text style={styles.upcomingTitle}>Upcoming Booking</Text>
            <TouchableOpacity onPress={() => router.push('/(tabs)/bookings')}><Text style={styles.viewAll}>View All</Text></TouchableOpacity>
          </View>
          {bookingsLoading ? (
            <View style={styles.upcomingRow}><ActivityIndicator color={PRIMARY} /></View>
          ) : upcoming ? (
            <TouchableOpacity style={styles.upcomingRow} onPress={() => router.push({ pathname: '/booking-detail', params: { id: String(upcoming.id) } })}>
              <View style={styles.calendarIcon}><Ionicons name="calendar-outline" size={34} color={PRIMARY} /></View>
              <View style={{ flex: 1 }}>
                <Text style={styles.vehicleName}>{upcoming.service?.name || upcoming.service?.title || 'Booking'}</Text>
                <Text style={styles.muted}>{upcoming.booking_date || ''} {upcoming.booking_time || ''}</Text>
                <Text style={styles.muted}>{upcoming.address || location?.fullAddress || ''}</Text>
              </View>
              <Text style={styles.confirmed}>{upcoming.status || 'pending'}</Text>
            </TouchableOpacity>
          ) : (
            <View style={styles.upcomingRow}><Text style={styles.muted}>No upcoming booking yet.</Text></View>
          )}
        </Card>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  content: { paddingHorizontal: 22, paddingBottom: 96 },
  greeting: { color: TEXT, fontSize: 27, fontWeight: '900', marginTop: 6 },
  locationRow: { flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 8 },
  locationText: { color: '#4E5868', fontSize: 17, fontWeight: '600' },
  errorBox: { marginTop: 12, borderWidth: 1, borderColor: '#FFD0D0', backgroundColor: '#FFF5F5', borderRadius: 14, padding: 12 },
  errorText: { color: '#B42318', fontSize: 14, fontWeight: '700' },
  retryText: { color: PRIMARY, fontSize: 13, fontWeight: '800', marginTop: 4 },
  search: { marginTop: 16, height: 62, borderRadius: 20, borderWidth: 1.4, borderColor: BORDER, paddingHorizontal: 18, flexDirection: 'row', alignItems: 'center' },
  searchInput: { flex: 1, fontSize: 18, color: TEXT, marginLeft: 12 },
  searchDivider: { width: 1, height: 32, backgroundColor: '#E4EAF2', marginRight: 14 },
  hero: { minHeight: 220, borderRadius: 22, overflow: 'hidden', marginTop: 20 },
  heroOverlay: { ...StyleSheet.absoluteFill, backgroundColor: 'rgba(171,231,250,0.42)' },
  heroOverlayEmpty: { backgroundColor: 'transparent' },
  heroArt: { position: 'absolute', right: -12, bottom: -10, width: 150, height: 150, alignItems: 'center', justifyContent: 'center' },
  heroArtBubble: { width: 112, height: 112, borderRadius: 56, backgroundColor: 'rgba(255,255,255,0.28)', alignItems: 'center', justifyContent: 'center' },
  heroArtSmallBubble: { position: 'absolute', right: 20, top: 16, width: 48, height: 48, borderRadius: 24, backgroundColor: 'rgba(11,74,169,0.62)', alignItems: 'center', justifyContent: 'center' },
  heroCopy: { padding: 22, paddingRight: 96 },
  heroTitle: { color: TEXT, fontSize: 29, lineHeight: 37, fontWeight: '900' },
  heroText: { color: TEXT, fontSize: 16, lineHeight: 24, marginTop: 12 },
  heroButton: { marginTop: 20, backgroundColor: '#0B4AA9', borderRadius: 16, minWidth: 152, alignSelf: 'flex-start', height: 56, paddingHorizontal: 18, alignItems: 'center', justifyContent: 'center', flexDirection: 'row', gap: 10 },
  heroButtonText: { color: '#fff', fontSize: 18, fontWeight: '900' },
  stateCard: { marginTop: 18, padding: 18, gap: 10, alignItems: 'center' },
  vehicleCard: { marginTop: 18, padding: 14, flexDirection: 'row', alignItems: 'center', gap: 12 },
  vehicleThumb: { width: 92, height: 66, borderRadius: 12, backgroundColor: '#E8F3FF', alignItems: 'center', justifyContent: 'center' },
  addVehicleCard: { marginTop: 18, padding: 18, flexDirection: 'row', alignItems: 'center', gap: 14 },
  addIcon: { width: 58, height: 58, borderRadius: 18, backgroundColor: '#EAF4FF', alignItems: 'center', justifyContent: 'center' },
  vehicleInfo: { flex: 1, minWidth: 0 },
  selectedWrap: { flexShrink: 0 },
  cardLabel: { color: PRIMARY, fontSize: 15, fontWeight: '800', marginBottom: 6 },
  vehicleName: { color: TEXT, fontSize: 19, lineHeight: 25, fontWeight: '900' },
  muted: { color: MUTED, fontSize: 14, lineHeight: 22, marginTop: 4 },
  categoryGrid: { flexDirection: 'row', gap: 14, marginTop: 20 },
  category: { flex: 1, minHeight: 134, borderRadius: 16, borderWidth: 1, borderColor: BORDER, backgroundColor: '#fff', alignItems: 'center', justifyContent: 'center', padding: 8, elevation: 3, shadowColor: '#0B4DA2', shadowOpacity: 0.08, shadowRadius: 10 },
  categoryIcon: { width: 66, height: 66, borderRadius: 22, alignItems: 'center', justifyContent: 'center', marginBottom: 12 },
  categoryText: { color: TEXT, fontSize: 13, fontWeight: '700', textAlign: 'center' },
  serviceCard: { marginTop: 22, flexDirection: 'row', overflow: 'hidden' },
  serviceImage: { width: 150, minHeight: 224 },
  serviceImageEmpty: { alignItems: 'center', justifyContent: 'center', backgroundColor: '#EEF4FA' },
  serviceBody: { flex: 1, padding: 14 },
  featureRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  featureBadge: { backgroundColor: PRIMARY, color: '#fff', paddingHorizontal: 10, paddingVertical: 5, borderRadius: 8, fontWeight: '800' },
  rating: { backgroundColor: SUCCESS, color: '#fff', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 8, fontWeight: '900' },
  serviceTitle: { color: TEXT, fontSize: 21, fontWeight: '900', marginTop: 12 },
  serviceDesc: { color: '#4F5B6D', fontSize: 14, lineHeight: 21, marginTop: 8 },
  metaRow: { flexDirection: 'row', alignItems: 'center', gap: 8, marginTop: 14, flexWrap: 'wrap' },
  metaStrong: { color: TEXT, fontSize: 13, fontWeight: '800', marginRight: 8 },
  priceRow: { marginTop: 12 },
  starts: { color: MUTED, textAlign: 'right', fontSize: 13, fontWeight: '600' },
  price: { color: PRIMARY, fontSize: 26, fontWeight: '900' },
  bookButton: { marginTop: 10, backgroundColor: PRIMARY, height: 48, borderRadius: 14, alignItems: 'center', justifyContent: 'center' },
  bookButtonText: { color: '#fff', fontSize: 17, fontWeight: '900' },
  upcoming: { marginTop: 22, overflow: 'hidden' },
  upcomingHead: { padding: 14, borderBottomWidth: 1, borderColor: '#EEF2F7', flexDirection: 'row', justifyContent: 'space-between' },
  upcomingTitle: { color: TEXT, fontSize: 17, fontWeight: '900' },
  viewAll: { color: PRIMARY, fontSize: 14, fontWeight: '800' },
  upcomingRow: { padding: 14, flexDirection: 'row', alignItems: 'center', gap: 14 },
  calendarIcon: { width: 70, height: 70, borderRadius: 18, backgroundColor: '#DDEEFF', alignItems: 'center', justifyContent: 'center' },
  confirmed: { backgroundColor: '#DDF8EA', color: '#079558', paddingHorizontal: 12, paddingVertical: 9, borderRadius: 10, fontWeight: '800', overflow: 'hidden' },
});

async function handleBannerPress(
  banner: AppBanner,
  selectService: (service: any) => Promise<void>,
  services: any[],
) {
  if (banner.redirect_type) {
    if (banner.redirect_type === 'service_detail') {
      const service = services.find((item) => String(item.id) === String(banner.redirect_value));
      if (service) await selectService(service);
    }
    await handleNotificationRedirect(banner);
    return;
  }

  if (banner.type === 'none') return;

  if (banner.type === 'service') {
    const service = services.find((item) => String(item.id) === String(banner.redirect_value));
    if (service) await selectService(service);
    router.push({ pathname: '/service-detail', params: { id: banner.redirect_value || '' } });
    return;
  }

  if (banner.type === 'booking') {
    router.push({ pathname: '/booking-detail', params: { id: banner.redirect_value || '' } });
    return;
  }

  if (banner.redirect_screen) {
    router.push(banner.redirect_screen as any);
  }
}
