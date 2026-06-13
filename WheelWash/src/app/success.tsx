import { Ionicons } from '@expo/vector-icons';
import { router, useLocalSearchParams } from 'expo-router';
import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PRIMARY, TEXT } from '@/lib/wheelwash-data';
import { Card, Logo, PrimaryButton } from '@/components/wheelwash/ui';

export default function SuccessScreen() {
  const { id } = useLocalSearchParams<{ id?: string }>();
  const bookingId = id || '';

  return (
    <SafeAreaView style={styles.root} edges={['top']}>
      <View style={styles.header}><Logo /></View>
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.check}>
          <Ionicons name="checkmark" size={88} color="#fff" />
        </View>
        <Text style={styles.title}>Booking Confirmed!</Text>
        <Text style={styles.subtitle}>Your assigned team has been notified.</Text>
        <Card style={styles.card}>
          <View style={styles.bookingId}>
            <Text style={styles.idLabel}>Booking ID</Text>
            <Text style={styles.id}>{bookingId || 'Confirmed'}</Text>
          </View>
          <Detail icon="car-sport-outline" label="Status" value="Booking created successfully" />
          <Detail icon="calendar-outline" label="Next Step" value="Track your booking for live updates." />
        </Card>
        <View style={styles.notice}>
          <Ionicons name="shield-checkmark" size={22} color="#14B86E" />
          <Text style={styles.noticeText}>We have notified the assigned team for your booking.</Text>
        </View>
        <PrimaryButton
          title="Track Booking"
          icon="car-sport-outline"
          style={{ width: '100%' }}
          onPress={() => bookingId && router.replace({ pathname: '/track', params: { id: bookingId } })}
          disabled={!bookingId}
        />
        <PrimaryButton title="Back to Home" icon="home" outline style={{ width: '100%' }} onPress={() => router.replace('/(tabs)')} />
      </ScrollView>
    </SafeAreaView>
  );
}

function Detail({ icon, label, value }: { icon: keyof typeof Ionicons.glyphMap; label: string; value: string }) {
  return (
    <View style={styles.detail}>
      <View style={styles.detailIcon}><Ionicons name={icon} size={28} color={PRIMARY} /></View>
      <View style={{ flex: 1 }}>
        <Text style={styles.detailLabel}>{label}</Text>
        <Text style={styles.detailValue}>{value}</Text>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  header: { paddingTop: 18, backgroundColor: '#fff', zIndex: 10, paddingBottom: 10 },
  content: { padding: 24, alignItems: 'center', gap: 20, paddingBottom: 28 },
  check: { marginTop: 20, width: 170, height: 170, borderRadius: 85, backgroundColor: '#37D27F', alignItems: 'center', justifyContent: 'center' },
  title: { color: TEXT, fontSize: 34, fontWeight: '900', marginTop: 10, textAlign: 'center' },
  subtitle: { color: '#34415A', fontSize: 20, lineHeight: 30, textAlign: 'center' },
  card: { width: '100%', marginTop: 18, overflow: 'hidden' },
  bookingId: { padding: 20, flexDirection: 'row', justifyContent: 'space-between', borderBottomWidth: 1, borderColor: '#E5ECF5' },
  idLabel: { color: '#34415A', fontSize: 17 },
  id: { color: PRIMARY, fontSize: 17, fontWeight: '900' },
  detail: { padding: 20, flexDirection: 'row', alignItems: 'center', gap: 16 },
  detailIcon: { width: 64, height: 64, borderRadius: 16, backgroundColor: '#EAF4FF', alignItems: 'center', justifyContent: 'center' },
  detailLabel: { color: '#34415A', fontSize: 16 },
  detailValue: { color: TEXT, fontSize: 18, lineHeight: 26, fontWeight: '900', marginTop: 5 },
  notice: { width: '100%', backgroundColor: '#EAF4FF', borderRadius: 12, padding: 16, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 10 },
  noticeText: { color: '#1F3659', fontSize: 15, fontWeight: '700', flex: 1, textAlign: 'center' },
});
