import { Ionicons } from '@expo/vector-icons';
import { router, useFocusEffect, useLocalSearchParams } from 'expo-router';
import { useCallback } from 'react';
import { ActivityIndicator, Image, ScrollView, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Card, PrimaryButton } from '@/components/wheelwash/ui';
import { MUTED, PRIMARY, TEXT } from '@/lib/wheelwash-data';
import { useServiceStore } from '@/store/serviceStore';

export default function ServiceDetailScreen() {
  const { id } = useLocalSearchParams<{ id?: string }>();
  const { services, selectedService, loadServices, selectService, loading, error } = useServiceStore();
  const service = selectedService || services.find((item) => String(item.id) === String(id)) || services[0];

  useFocusEffect(useCallback(() => { if (!service) loadServices(); }, [loadServices, service]));

  return (
    <SafeAreaView style={styles.root} edges={['top']}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.canGoBack() ? router.back() : router.replace('/')} style={styles.back}><Ionicons name="arrow-back" size={26} color={TEXT} /></TouchableOpacity>
        <Text style={styles.headerTitle}>Service Details</Text>
        <View style={styles.back} />
      </View>
      <ScrollView style={styles.scroll} showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        {loading && <Card style={styles.state}><ActivityIndicator color={PRIMARY} /><Text style={styles.desc}>Loading service...</Text></Card>}
        {error && <TouchableOpacity style={styles.errorBox} onPress={loadServices}><Text style={styles.errorText}>{error}</Text><Text style={styles.retryText}>Retry</Text></TouchableOpacity>}
        {service ? (
          <>
            <Card style={styles.heroCard}>
              {service.image_url || service.image ? (
                <Image source={{ uri: service.image_url || service.image }} style={styles.heroImage} resizeMode="cover" />
              ) : (
                <View style={[styles.heroImage, styles.heroImageEmpty]}><Ionicons name="image-outline" size={44} color={MUTED} /></View>
              )}
              <View style={styles.detailBody}>
                <View style={styles.titleRow}>
                  <View style={styles.titleContent}>
                    <Text style={styles.badge}>Featured</Text>
                    <Text style={styles.title}>{service.name || service.title || 'Service'}</Text>
                  </View>
                  <View style={styles.rating}><Text style={styles.ratingText}>4.8</Text></View>
                </View>
                <Text style={styles.desc}>{service.short_description || service.description || 'Professional car wash service.'}</Text>
                <View style={styles.features}>
                  <Feature icon="time-outline" title={`${service.duration_minutes || service.duration || 45} min`} sub="Duration" />
                  <Feature icon="water-outline" title="Water Efficient" sub="Eco-friendly" />
                  <Feature icon="shield-checkmark-outline" title="Premium Care" sub="Safe for your car" />
                </View>
                <Text style={styles.price}>Rs {service.price || 0} <Text style={styles.tax}>Inclusive of all taxes</Text></Text>
              </View>
            </Card>
            <PrimaryButton title="Select Date & Time" icon="arrow-forward" onPress={async () => { await selectService(service); router.push('/select-slot'); }} />
          </>
        ) : (
          <Card style={styles.state}><Text style={styles.title}>No service selected</Text><Text style={styles.desc}>Choose a service from the services page.</Text></Card>
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

function Feature({ icon, title, sub }: { icon: keyof typeof Ionicons.glyphMap; title: string; sub: string }) {
  return (
    <View style={styles.feature}>
      <Ionicons name={icon} size={28} color={PRIMARY} />
      <View><Text style={styles.featureTitle}>{title}</Text><Text style={styles.featureSub}>{sub}</Text></View>
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  header: { height: 64, paddingHorizontal: 22, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  back: { width: 42, height: 42, alignItems: 'center', justifyContent: 'center' },
  headerTitle: { color: TEXT, fontSize: 26, fontWeight: '900' },
  scroll: { flex: 1 },
  content: { padding: 22, gap: 18, paddingBottom: 60 },
  heroCard: { overflow: 'hidden' },
  heroImage: { width: '100%', height: 260 },
  heroImageEmpty: { alignItems: 'center', justifyContent: 'center', backgroundColor: '#EEF4FA' },
  detailBody: { padding: 22 },
  titleRow: { flexDirection: 'row', justifyContent: 'space-between', gap: 14, alignItems: 'flex-start' },
  titleContent: { flex: 1, alignItems: 'flex-start' },
  badge: { alignSelf: 'flex-start', backgroundColor: PRIMARY, color: '#fff', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 7, fontSize: 15, fontWeight: '900' },
  title: { color: TEXT, fontSize: 31, fontWeight: '900', marginTop: 12 },
  rating: { backgroundColor: '#12A968', paddingHorizontal: 11, paddingVertical: 8, borderRadius: 9, alignSelf: 'flex-start' },
  ratingText: { color: '#fff', fontSize: 18, fontWeight: '900' },
  desc: { color: '#4E5868', fontSize: 17, lineHeight: 27, marginTop: 12 },
  features: { borderTopWidth: 1, borderBottomWidth: 1, borderColor: '#E5ECF5', paddingVertical: 18, marginTop: 18, gap: 14 },
  feature: { flexDirection: 'row', alignItems: 'center', gap: 12 },
  featureTitle: { color: TEXT, fontSize: 16, fontWeight: '900' },
  featureSub: { color: MUTED, fontSize: 14, marginTop: 2 },
  price: { color: PRIMARY, fontSize: 36, fontWeight: '900', marginTop: 18 },
  tax: { color: MUTED, fontSize: 15, fontWeight: '500' },
  state: { padding: 22, alignItems: 'center' },
  errorBox: { borderWidth: 1, borderColor: '#FFD0D0', backgroundColor: '#FFF5F5', borderRadius: 14, padding: 12 },
  errorText: { color: '#B42318', fontSize: 14, fontWeight: '700' },
  retryText: { color: PRIMARY, fontSize: 13, fontWeight: '800', marginTop: 4 },
});
