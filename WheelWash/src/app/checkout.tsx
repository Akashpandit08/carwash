import AsyncStorage from '@react-native-async-storage/async-storage';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import { useFocusEffect } from 'expo-router';
import { useCallback, useState } from 'react';
import { ActivityIndicator, Alert, Image, ScrollView, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { WashType } from '@/api/bookingApi';
import { Card, PrimaryButton, SelectedBadge } from '@/components/wheelwash/ui';
import { BORDER, MUTED, PRIMARY, TEXT } from '@/lib/wheelwash-data';
import { useBookingStore } from '@/store/bookingStore';
import { useLocationStore } from '@/store/locationStore';
import { useServiceStore } from '@/store/serviceStore';
import { useVehicleStore } from '@/store/vehicleStore';
import { listMySubscriptions, CustomerSubscriptionDto } from '@/api/subscriptionApi';

import { getISTDateString } from '@/utils/date';

function todayString() {
  return getISTDateString();
}

export default function CheckoutScreen() {
  const { selectedVehicle } = useVehicleStore();
  const { selectedService } = useServiceStore();
  const { location } = useLocationStore();
  const { createBooking, loading, error } = useBookingStore();
  const [bookingDate, setBookingDate] = useState(todayString());
  const [bookingTime, setBookingTime] = useState('10:00');
  const [washType, setWashType] = useState<WashType>('door_to_door');
  const [subscriptions, setSubscriptions] = useState<CustomerSubscriptionDto[]>([]);
  const [selectedSubscription, setSelectedSubscription] = useState<CustomerSubscriptionDto | null>(null);

  useFocusEffect(useCallback(() => {
    AsyncStorage.multiGet(['booking_date', 'booking_time', 'wash_type']).then((values) => {
      const map = Object.fromEntries(values);
      setBookingDate(map.booking_date || todayString());
      setBookingTime(map.booking_time || '10:00');
      setWashType((map.wash_type as WashType) || 'door_to_door');
    });

    listMySubscriptions().then(subs => {
      const active = subs.filter(s => s.status === 'active' && s.remaining_washes && s.remaining_washes > 0);
      setSubscriptions(active);
    }).catch(() => {});
  }, []));

  const book = async () => {
    if (!selectedVehicle || !selectedService || !location) {
      Alert.alert('Missing details', 'Please select vehicle, service and location before booking.');
      return;
    }
    try {
      const booking = await createBooking({
        vehicleId: selectedVehicle.id,
        serviceId: selectedService.id,
        washType,
        bookingDate,
        bookingTime,
        location,
        paymentMethod: selectedSubscription ? 'subscription' : 'cod',
        customerSubscriptionId: selectedSubscription?.id,
      });
      router.replace({ pathname: '/success', params: { id: String(booking.id) } });
    } catch (err) {
      Alert.alert('Booking failed', err instanceof Error ? err.message : 'Please try again.');
    }
  };

  return (
    <SafeAreaView style={styles.root} edges={['top']}>
      <View style={styles.header}>
        <TouchableOpacity style={styles.back} onPress={() => router.canGoBack() ? router.back() : router.replace('/')}><Ionicons name="arrow-back" size={26} color={TEXT} /></TouchableOpacity>
        <Text style={styles.headerTitle}>Booking Summary</Text>
        <View style={styles.back} />
      </View>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        {error && <Text style={styles.errorText}>{error}</Text>}
        <Card style={[styles.serviceCard, { flexDirection: 'column', gap: 12 }]}>
          <View style={{ flexDirection: 'row', gap: 16 }}>
            {selectedService?.image_url || selectedService?.image ? (
              <Image source={{ uri: selectedService.image_url || selectedService.image }} style={styles.serviceImage} />
            ) : (
              <View style={[styles.serviceImage, styles.serviceImageEmpty]}><Ionicons name="image-outline" size={32} color={MUTED} /></View>
            )}
            <View style={{ flex: 1 }}>
              <Text style={styles.title}>{selectedService?.name || selectedService?.title || 'Selected Service'}</Text>
              <Text style={styles.sub}>{selectedService?.short_description || selectedService?.description || 'Professional car wash service.'}</Text>
            </View>
          </View>
          <View style={{ borderTopWidth: 1, borderTopColor: '#EEF2F7', paddingTop: 12, alignItems: 'flex-end' }}>
            <Text style={styles.price}>Rs {selectedService?.price || 0}</Text>
          </View>
        </Card>
        <InfoCard icon="calendar-outline" label="Date & Time" title={bookingDate} sub={bookingTime} />
        <InfoCard icon="car-outline" label="Wash Type" title={washType === 'pickup_wash' ? 'Pickup Wash' : 'Door to Door'} sub="COD payment selected" />
        <InfoCard 
          icon="car-sport" 
          label="Your Vehicle" 
          title={selectedVehicle ? `${selectedVehicle.brand} ${selectedVehicle.model}` : 'No vehicle selected'} 
          sub={selectedVehicle ? selectedVehicle.registrationNumber : 'Please select a vehicle'} 
        />
        <InfoCard icon="location-outline" label="Service Address" title={location?.city || 'No location'} sub={location?.fullAddress || 'Save location before booking'} />
        
        {subscriptions.length > 0 && (
          <View style={{ gap: 12 }}>
            <Text style={{ color: TEXT, fontSize: 18, fontWeight: '900', paddingHorizontal: 4 }}>Apply Subscription</Text>
            {subscriptions.map(sub => (
              <TouchableOpacity key={sub.id} onPress={() => setSelectedSubscription(sub.id === selectedSubscription?.id ? null : sub)} activeOpacity={0.7}>
                <Card style={[styles.infoCard, { borderColor: sub.id === selectedSubscription?.id ? PRIMARY : 'transparent', borderWidth: 2 }]}>
                  <View style={styles.infoIcon}><Ionicons name="card-outline" size={32} color={PRIMARY} /></View>
                  <View style={{ flex: 1 }}>
                    <Text style={styles.blue}>{sub.subscription_plan?.name}</Text>
                    <Text style={styles.titleSmall}>{sub.remaining_washes} Washes Remaining</Text>
                  </View>
                  {sub.id === selectedSubscription?.id && <Ionicons name="checkmark-circle" size={28} color={PRIMARY} />}
                </Card>
              </TouchableOpacity>
            ))}
          </View>
        )}

        <Card style={styles.bill}>
          <Line label="Service Price" value={`Rs ${selectedService?.price || 0}`} />
          {selectedSubscription && <Line label="Subscription Applied" value={`- Rs ${selectedService?.price || 0}`} />}
          <Line label="Payment Method" value={selectedSubscription ? "Subscription" : "COD"} />
          <View style={styles.billDivider} />
          <Line label="Total Amount" value={`Rs ${selectedSubscription ? 0 : (selectedService?.price || 0)}`} total />
        </Card>
        <PrimaryButton title={loading ? 'Creating Booking...' : (selectedSubscription ? 'Book with Subscription' : 'Create COD Booking')} onPress={book} />
        {loading && <ActivityIndicator color={PRIMARY} />}
      </ScrollView>
    </SafeAreaView>
  );
}

function InfoCard({ icon, label, title, sub }: { icon: keyof typeof Ionicons.glyphMap; label: string; title: string; sub: string }) {
  return (
    <Card style={styles.infoCard}>
      <View style={styles.infoIcon}><Ionicons name={icon} size={32} color={PRIMARY} /></View>
      <View style={{ flex: 1 }}><Text style={styles.blue}>{label}</Text><Text style={styles.titleSmall}>{title}</Text><Text style={styles.sub}>{sub}</Text></View>
    </Card>
  );
}

function Line({ label, value, total }: { label: string; value: string; total?: boolean }) {
  return <View style={styles.line}><Text style={[styles.lineLabel, total && styles.totalLabel]}>{label}</Text><Text style={[styles.lineValue, total && styles.totalValue]}>{value}</Text></View>;
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  header: { height: 64, paddingHorizontal: 22, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  back: { width: 42, height: 42, alignItems: 'center', justifyContent: 'center' },
  headerTitle: { color: TEXT, fontSize: 25, fontWeight: '900' },
  content: { padding: 22, gap: 18, paddingBottom: 28 },
  serviceCard: { padding: 16, flexDirection: 'row', gap: 16 },
  serviceImage: { width: 126, height: 144, borderRadius: 12 },
  serviceImageEmpty: { alignItems: 'center', justifyContent: 'center', backgroundColor: '#EEF4FA' },
  title: { color: TEXT, fontSize: 23, fontWeight: '900' },
  sub: { color: MUTED, fontSize: 15, lineHeight: 23, marginTop: 6 },
  price: { color: PRIMARY, fontSize: 26, fontWeight: '900', alignSelf: 'flex-end' },
  vehicle: { padding: 16, flexDirection: 'row', alignItems: 'center', gap: 14 },
  vehicleThumb: { width: 92, height: 66, borderRadius: 12, backgroundColor: '#E8F3FF', alignItems: 'center', justifyContent: 'center' },
  infoCard: { padding: 18, flexDirection: 'row', alignItems: 'center', gap: 16 },
  infoIcon: { width: 70, height: 70, borderRadius: 18, backgroundColor: '#EAF4FF', alignItems: 'center', justifyContent: 'center' },
  blue: { color: PRIMARY, fontSize: 16, fontWeight: '900' },
  titleSmall: { color: TEXT, fontSize: 21, fontWeight: '900', marginTop: 8 },
  bill: { padding: 20 },
  line: { flexDirection: 'row', justifyContent: 'space-between', marginVertical: 10 },
  lineLabel: { color: TEXT, fontSize: 18 },
  lineValue: { color: TEXT, fontSize: 18, fontWeight: '700' },
  billDivider: { height: 1, backgroundColor: BORDER, marginVertical: 14 },
  totalLabel: { fontSize: 22, fontWeight: '900' },
  totalValue: { color: PRIMARY, fontSize: 32, fontWeight: '900' },
  errorText: { color: '#B42318', fontSize: 14, fontWeight: '700' },
});
