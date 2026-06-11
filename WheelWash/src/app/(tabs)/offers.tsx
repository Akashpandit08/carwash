import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from 'expo-router';
import { useCallback, useState } from 'react';
import { ActivityIndicator, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { getHome } from '@/api/customerApi';
import { Card, ScreenHeader } from '@/components/wheelwash/ui';
import { MUTED, PRIMARY, TEXT } from '@/lib/wheelwash-data';

type Coupon = {
  id?: string | number;
  code: string;
  description?: string;
  discount_type?: string;
  discount_value?: string | number;
};

export default function OffersTab() {
  const [coupons, setCoupons] = useState<Coupon[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const home = await getHome() as { coupons?: Coupon[] };
      setCoupons(home.coupons || []);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Offer load failed.');
    } finally {
      setLoading(false);
    }
  }, []);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  return (
    <SafeAreaView style={styles.root} edges={['top']}>
      <ScreenHeader title="Offers" />
      <ScrollView contentContainerStyle={styles.content}>
        {loading && <View style={styles.state}><ActivityIndicator color={PRIMARY} /><Text style={styles.subtitle}>Loading offers...</Text></View>}
        {error && <Card style={styles.state}><Text style={styles.error}>{error}</Text></Card>}
        {!loading && !error && coupons.length === 0 && (
          <Card style={styles.state}><Text style={styles.title}>No active offers</Text><Text style={styles.subtitle}>Check again later.</Text></Card>
        )}
        {coupons.map((coupon) => (
          <Card key={String(coupon.id || coupon.code)} style={styles.offer}>
            <View style={styles.icon}><Ionicons name="pricetag" size={28} color={PRIMARY} /></View>
            <View style={{ flex: 1 }}>
              <Text style={styles.code}>{coupon.code}</Text>
              <Text style={styles.title}>{coupon.description || 'WheelWash offer'}</Text>
              <Text style={styles.subtitle}>{coupon.discount_type === 'percentage' ? `${coupon.discount_value}% discount` : `Rs ${coupon.discount_value} discount`}</Text>
            </View>
            <Text style={styles.apply}>Apply</Text>
          </Card>
        ))}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  content: { padding: 22, gap: 16 },
  state: { padding: 18, alignItems: 'center', gap: 10 },
  offer: { padding: 18, flexDirection: 'row', alignItems: 'center', gap: 16 },
  icon: { width: 58, height: 58, borderRadius: 18, backgroundColor: '#EAF4FF', alignItems: 'center', justifyContent: 'center' },
  code: { color: PRIMARY, fontSize: 15, fontWeight: '900' },
  title: { color: TEXT, fontSize: 19, fontWeight: '900', marginTop: 4 },
  subtitle: { color: MUTED, fontSize: 14, marginTop: 5 },
  apply: { color: PRIMARY, fontSize: 16, fontWeight: '900' },
  error: { color: '#B42318', fontSize: 14, fontWeight: '800' },
});
