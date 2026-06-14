import AsyncStorage from '@react-native-async-storage/async-storage';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import { useEffect, useMemo, useState } from 'react';
import { ActivityIndicator, Alert, Image, ScrollView, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Card, PrimaryButton, SelectedBadge } from '@/components/wheelwash/ui';
import { BORDER, PRIMARY, STORAGE_KEYS, TEXT } from '@/lib/wheelwash-data';
import { AvailableSlot, getAvailableSlots, WashType } from '@/api/bookingApi';
import { useLocationStore } from '@/store/locationStore';
import { useServiceStore } from '@/store/serviceStore';
import { useVehicleStore } from '@/store/vehicleStore';

const washTypes: Array<{ label: string; value: WashType; icon: keyof typeof Ionicons.glyphMap }> = [
  { label: 'Door to Door', value: 'door_to_door', icon: 'home-outline' },
  { label: 'Pickup Wash', value: 'pickup_wash', icon: 'car-outline' },
];

import { getISTDateString } from '@/utils/date';

function nextDays(count = 7) {
  return Array.from({ length: count }, (_, index) => getISTDateString(index));
}

export default function SelectSlotScreen() {
  const days = useMemo(() => nextDays(), []);
  const [bookingDate, setBookingDate] = useState(days[0]);
  const [bookingTime, setBookingTime] = useState('');
  const [washType, setWashType] = useState<WashType>('door_to_door');
  const [slots, setSlots] = useState<AvailableSlot[]>([]);
  const [slotsLoading, setSlotsLoading] = useState(false);
  const [slotsError, setSlotsError] = useState<string | null>(null);
  const { selectedVehicle } = useVehicleStore();
  const { selectedService, loadServices } = useServiceStore();
  const { location } = useLocationStore();

  useEffect(() => {
    loadServices();
  }, [loadServices]);

  useEffect(() => {
    let isActive = true;

    async function loadSlots() {
      if (location?.latitude == null || location?.longitude == null) {
        setSlots([]);
        setBookingTime('');
        setSlotsError('Save a location with map coordinates to see available slots.');
        return;
      }

      setSlotsLoading(true);
      setSlotsError(null);
      try {
        const result = await getAvailableSlots({
          serviceId: selectedService?.id,
          washType,
          latitude: location.latitude,
          longitude: location.longitude,
          date: bookingDate,
        });
        if (!isActive) return;

        const uniqueResult = result.filter((slot, index, self) => index === self.findIndex(s => s.time === slot.time));
        const available = uniqueResult.filter((slot) => slot.available);
        setSlots(uniqueResult);
        setBookingTime((current) => available.some((slot) => slot.time === current) ? current : available[0]?.time || '');
      } catch (err) {
        if (!isActive) return;
        setSlots([]);
        setBookingTime('');
        setSlotsError(err instanceof Error ? err.message : 'Slot availability failed.');
      } finally {
        if (isActive) setSlotsLoading(false);
      }
    }

    loadSlots();

    return () => {
      isActive = false;
    };
  }, [bookingDate, location?.latitude, location?.longitude, selectedService?.id, washType]);

  const onContinue = async () => {
    if (!selectedVehicle) {
      Alert.alert('Vehicle required', 'Please add or select a vehicle first.');
      router.push('/add-vehicle');
      return;
    }
    if (!selectedService) {
      Alert.alert('Service required', 'Please select a service first.');
      router.push('/services');
      return;
    }
    if (!location) {
      Alert.alert('Location required', 'Please save your service location first.');
      router.push('/location');
      return;
    }
    if (location.latitude == null || location.longitude == null) {
      Alert.alert('Location coordinates required', 'Please save your service location from the map before selecting a slot.');
      router.push('/location');
      return;
    }
    if (!bookingTime) {
      Alert.alert('Slot required', 'Please choose an available slot.');
      return;
    }
    await AsyncStorage.multiSet([
      [STORAGE_KEYS.serviceId, String(selectedService.id)],
      [STORAGE_KEYS.vehicleId, String(selectedVehicle.id)],
      ['booking_date', bookingDate],
      ['booking_time', bookingTime],
      ['wash_type', washType],
    ]);
    router.push('/checkout');
  };

  return (
    <SafeAreaView style={styles.root} edges={['top']}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.canGoBack() ? router.back() : router.replace('/')} style={styles.back}><Ionicons name="arrow-back" size={26} color={TEXT} /></TouchableOpacity>
        <Text style={styles.headerTitle}>Select Wash & Slot</Text>
        <View style={styles.back} />
      </View>
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <Text style={styles.blueTitle}>Your Vehicle</Text>
        {selectedVehicle ? (
          <Card style={styles.vehicle}>
            <View style={styles.vehicleThumb}><Ionicons name="car-sport" size={34} color={PRIMARY} /></View>
            <View style={{ flex: 1 }}>
              <Text style={styles.vehicleName}>{selectedVehicle.brand} {selectedVehicle.model}</Text>
              <Text style={styles.vehicleSub}>{selectedVehicle.registrationNumber} - {selectedVehicle.color}</Text>
            </View>
            <SelectedBadge />
          </Card>
        ) : <Empty title="No vehicle selected" />}

        <Text style={styles.blueTitle}>Selected Service</Text>
        {selectedService ? (
          <Card style={styles.serviceCard}>
            <View style={styles.serviceTop}>
              {selectedService.image_url || selectedService.image ? (
                <Image source={{ uri: selectedService.image_url || selectedService.image }} style={styles.serviceImage} />
              ) : (
                <View style={[styles.serviceImage, styles.serviceImageEmpty]}><Ionicons name="image-outline" size={30} color="#586274" /></View>
              )}
              <View style={styles.serviceInfo}>
                <Text style={styles.badge}>Selected</Text>
                <Text style={styles.serviceName} numberOfLines={2}>{selectedService.name || selectedService.title || 'Service'}</Text>
                <Text style={styles.vehicleSub} numberOfLines={2}>{selectedService.short_description || selectedService.description || 'Professional service.'}</Text>
              </View>
            </View>
            <View style={styles.serviceBottom}>
              <Text style={styles.duration}>{selectedService.duration_minutes || selectedService.duration || 45} min</Text>
              <Text style={styles.price}>Rs {selectedService.price || 0}</Text>
            </View>
          </Card>
        ) : <Empty title="No service selected" />}

        <Text style={styles.sectionTitle}>Wash Type</Text>
        <View style={styles.washGrid}>
          {washTypes.map((type) => (
            <TouchableOpacity key={type.value} style={[styles.washType, washType === type.value && styles.timeActive]} onPress={() => setWashType(type.value)} activeOpacity={0.85}>
              <Ionicons name={type.icon} size={24} color={washType === type.value ? '#fff' : PRIMARY} />
              <Text style={[styles.timeText, washType === type.value && styles.timeTextActive]}>{type.label}</Text>
            </TouchableOpacity>
          ))}
        </View>

        <Text style={styles.sectionTitle}>Service Address</Text>
        <TouchableOpacity style={styles.addressCard} onPress={() => router.push('/location')} activeOpacity={0.85}>
          <View style={styles.addressIcon}><Ionicons name="location-outline" size={28} color={PRIMARY} /></View>
          <View style={{ flex: 1 }}>
            <Text style={styles.addressTitle}>{location?.city || 'Select address'}</Text>
            <Text style={styles.vehicleSub}>{location?.fullAddress || 'Choose a map location to check nearby staff.'}</Text>
          </View>
          <Ionicons name="chevron-forward" size={22} color="#667085" />
        </TouchableOpacity>

        <Text style={styles.sectionTitle}>Select Date</Text>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.days}>
          {days.map((day) => (
            <TouchableOpacity key={day} style={[styles.day, bookingDate === day && styles.dayActive]} onPress={() => setBookingDate(day)}>
              <Text style={[styles.dayText, bookingDate === day && styles.dayTextActive]}>{day}</Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        <Text style={styles.sectionTitle}>Select Time</Text>
        {slotsLoading ? (
          <Card style={styles.slotState}><ActivityIndicator color={PRIMARY} /><Text style={styles.vehicleSub}>Checking nearby availability...</Text></Card>
        ) : slotsError ? (
          <Card style={styles.slotState}><Text style={styles.errorText}>{slotsError}</Text></Card>
        ) : slots.length ? (
          <View style={styles.timeGrid}>
            {slots.map((slot) => {
              const disabled = !slot.available;
              return (
                <TouchableOpacity
                  key={slot.time}
                  style={[styles.time, bookingTime === slot.time && styles.timeActive, disabled && styles.timeDisabled]}
                  onPress={() => !disabled && setBookingTime(slot.time)}
                  disabled={disabled}
                >
                  <Text style={[styles.timeText, bookingTime === slot.time && styles.timeTextActive, disabled && styles.timeTextDisabled]}>{slot.time}</Text>
                </TouchableOpacity>
              );
            })}
          </View>
        ) : (
          <Card style={styles.slotState}><Text style={styles.vehicleSub}>No slots available for this date.</Text></Card>
        )}

        <Card style={styles.summary}>
          <Ionicons name="calendar-outline" size={42} color={PRIMARY} />
          <View style={{ flex: 1 }}><Text style={styles.summaryTitle}>Booking Summary</Text><Text style={styles.vehicleSub}>{bookingDate}{bookingTime ? ` at ${bookingTime}` : ''}</Text></View>
          <Text style={styles.price}>Rs {selectedService?.price || 0}</Text>
        </Card>
        <PrimaryButton title="Continue" onPress={onContinue} disabled={!bookingTime || slotsLoading} />
      </ScrollView>
    </SafeAreaView>
  );
}

function Empty({ title }: { title: string }) {
  return <Card style={styles.empty}><Text style={styles.vehicleSub}>{title}</Text></Card>;
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  header: { height: 64, paddingHorizontal: 22, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  back: { width: 42, height: 42, alignItems: 'center', justifyContent: 'center' },
  headerTitle: { color: TEXT, fontSize: 26, fontWeight: '900' },
  content: { padding: 22, gap: 18, paddingBottom: 28 },
  blueTitle: { color: PRIMARY, fontSize: 20, fontWeight: '900' },
  vehicle: { padding: 16, flexDirection: 'row', alignItems: 'center', gap: 14 },
  vehicleThumb: { width: 110, height: 80, borderRadius: 14, backgroundColor: '#E8F3FF', alignItems: 'center', justifyContent: 'center' },
  vehicleName: { color: TEXT, fontSize: 23, fontWeight: '900' },
  vehicleSub: { color: '#586274', fontSize: 16, lineHeight: 24, marginTop: 5 },
  serviceCard: { padding: 16, gap: 16 },
  serviceTop: { flexDirection: 'row', gap: 16 },
  serviceInfo: { flex: 1 },
  serviceBottom: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', borderTopWidth: 1, borderTopColor: '#EEF2F7', paddingTop: 16 },
  serviceImage: { width: 112, height: 112, borderRadius: 12 },
  serviceImageEmpty: { alignItems: 'center', justifyContent: 'center', backgroundColor: '#EEF4FA' },
  badge: { alignSelf: 'flex-start', backgroundColor: PRIMARY, color: '#fff', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 7, fontWeight: '900' },
  serviceName: { color: TEXT, fontSize: 22, fontWeight: '900', marginTop: 10 },
  price: { color: PRIMARY, fontSize: 25, fontWeight: '900' },
  duration: { color: '#586274', fontSize: 16, fontWeight: '700' },
  sectionTitle: { color: TEXT, fontSize: 21, fontWeight: '900', marginTop: 4 },
  days: { gap: 12 },
  day: { minWidth: 116, height: 64, borderRadius: 14, borderWidth: 1, borderColor: BORDER, alignItems: 'center', justifyContent: 'center', backgroundColor: '#fff', paddingHorizontal: 12 },
  dayActive: { backgroundColor: PRIMARY, borderColor: PRIMARY },
  dayText: { color: TEXT, fontSize: 15, fontWeight: '700' },
  dayTextActive: { color: '#fff' },
  timeGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  washGrid: { flexDirection: 'row', gap: 12 },
  washType: { flex: 1, minHeight: 68, borderRadius: 14, borderWidth: 1, borderColor: BORDER, alignItems: 'center', justifyContent: 'center', gap: 8, paddingHorizontal: 10 },
  addressCard: { minHeight: 86, borderRadius: 14, borderWidth: 1, borderColor: BORDER, flexDirection: 'row', alignItems: 'center', gap: 14, padding: 14, backgroundColor: '#fff' },
  addressIcon: { width: 54, height: 54, borderRadius: 14, alignItems: 'center', justifyContent: 'center', backgroundColor: '#EAF4FF' },
  addressTitle: { color: TEXT, fontSize: 18, fontWeight: '900' },
  time: { minWidth: '31%', minHeight: 58, borderRadius: 14, borderWidth: 1, borderColor: BORDER, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 10 },
  timeActive: { backgroundColor: PRIMARY, borderColor: PRIMARY },
  timeDisabled: { backgroundColor: '#F3F6FA', borderColor: '#E5ECF5' },
  timeText: { color: '#4F5B6D', fontSize: 15, fontWeight: '700', textAlign: 'center' },
  timeTextActive: { color: '#fff' },
  timeTextDisabled: { color: '#A0A9B7' },
  summary: { padding: 16, flexDirection: 'row', alignItems: 'center', gap: 16, backgroundColor: '#F1F8FF' },
  summaryTitle: { color: TEXT, fontSize: 18, fontWeight: '900' },
  empty: { padding: 16 },
  slotState: { padding: 18, alignItems: 'center', gap: 10 },
  errorText: { color: '#B42318', fontSize: 15, fontWeight: '700', textAlign: 'center' },
});
