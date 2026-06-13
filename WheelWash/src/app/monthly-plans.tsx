import React, { useCallback, useState } from 'react';
import { ActivityIndicator, Alert, ScrollView, StatusBar, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useFocusEffect, useRouter } from 'expo-router';
import { listSubscriptionPlans, purchaseSubscription, SubscriptionPlanDto } from '@/api/subscriptionApi';
import { Brand, Radius, Shadow, Spacing, Typography } from '@/constants/theme';
import { getLocation } from '@/lib/wheelwash-storage';

export default function MonthlyPlansScreen() {
  const router = useRouter();
  const [plans, setPlans] = useState<SubscriptionPlanDto[]>([]);
  const [city, setCity] = useState('');
  const [loading, setLoading] = useState(true);
  const [buyingId, setBuyingId] = useState<string | number | null>(null);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const [remotePlans, location] = await Promise.all([listSubscriptionPlans(), getLocation()]);
      setPlans(remotePlans);
      setCity(location?.city || '');
    } finally {
      setLoading(false);
    }
  }, []);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  const buy = async (plan: SubscriptionPlanDto) => {
    setBuyingId(plan.id);
    try {
      await purchaseSubscription(plan.id, { payment_method: 'cod' });
      Alert.alert('Plan Activated', 'Your monthly plan is ready to use.');
    } catch (error: any) {
      Alert.alert('Purchase failed', error.message || 'Unable to purchase this plan.');
    } finally {
      setBuyingId(null);
    }
  };

  return (
    <View style={styles.root}>
      <StatusBar barStyle="dark-content" backgroundColor={Brand.white} />
      <SafeAreaView edges={['top']} style={{ backgroundColor: Brand.white }}>
        <View style={styles.topBar}>
          <TouchableOpacity onPress={() => router.canGoBack() ? router.back() : router.replace('/')} style={styles.iconBtn}><Text style={styles.iconText}>Back</Text></TouchableOpacity>
          <Text style={styles.headerTitle}>Monthly Plans</Text>
          <View style={styles.iconBtn} />
        </View>
      </SafeAreaView>
      <ScrollView style={styles.scroll} contentContainerStyle={styles.content}>
        {!!city && <Text style={styles.cityText}>Showing plans for {city}</Text>}
        {loading && <View style={styles.state}><ActivityIndicator color={Brand.royalBlue} /><Text style={styles.muted}>Loading plans...</Text></View>}
        {!loading && plans.length === 0 && <View style={styles.state}><Text style={styles.emptyTitle}>No plans found</Text><Text style={styles.muted}>Select Agra or Firozabad to see monthly plans.</Text></View>}
        {plans.map((plan) => (
          <View key={String(plan.id)} style={styles.card}>
            <Text style={styles.planName}>{plan.name}</Text>
            <Text style={styles.price}>Rs {plan.price}</Text>
            <Text style={styles.detail}>{plan.total_washes} washes in {plan.duration_days} days</Text>
            <Text style={styles.detail}>Exterior {plan.exterior_washes} | Interior {plan.interior_washes} | Foam {plan.foam_washes}</Text>
            <Text style={styles.detail}>Weekly limit: {plan.max_washes_per_week || 'No limit'}</Text>
            <View style={styles.benefits}>
              {plan.doorstep_included && <Text style={styles.badge}>Doorstep</Text>}
              {plan.pickup_drop_included && <Text style={styles.badge}>Pickup/drop</Text>}
              {plan.priority_booking && <Text style={styles.badge}>Priority</Text>}
              {plan.vacuum_included && <Text style={styles.badge}>Vacuum</Text>}
            </View>
            <TouchableOpacity style={styles.buyBtn} onPress={() => buy(plan)} disabled={buyingId === plan.id}>
              <Text style={styles.buyText}>{buyingId === plan.id ? 'Activating...' : 'Buy Plan'}</Text>
            </TouchableOpacity>
          </View>
        ))}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: Brand.offWhite },
  scroll: { flex: 1 },
  content: { padding: Spacing.xl, gap: Spacing.md, paddingBottom: Spacing.xxl },
  topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: Spacing.md, paddingVertical: Spacing.sm, backgroundColor: Brand.white, borderBottomWidth: 1, borderBottomColor: Brand.borderLight },
  iconBtn: { minWidth: 52, height: 40, alignItems: 'center', justifyContent: 'center', borderRadius: Radius.round, backgroundColor: Brand.offWhite },
  iconText: { ...Typography.caption, color: Brand.textPrimary, fontWeight: '800' },
  headerTitle: { ...Typography.h2, color: Brand.textPrimary },
  cityText: { ...Typography.bodyMed, color: Brand.textSecondary },
  state: { padding: Spacing.xl, alignItems: 'center', gap: Spacing.sm },
  emptyTitle: { ...Typography.h3, color: Brand.textPrimary },
  muted: { ...Typography.body, color: Brand.textSecondary, textAlign: 'center' },
  card: { backgroundColor: Brand.white, borderRadius: Radius.xl, padding: Spacing.lg, ...Shadow.subtle },
  planName: { ...Typography.h2, color: Brand.textPrimary },
  price: { ...Typography.price, color: Brand.royalBlue, marginTop: Spacing.sm },
  detail: { ...Typography.body, color: Brand.textSecondary, marginTop: 6 },
  benefits: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginTop: Spacing.md },
  badge: { ...Typography.caption, color: Brand.royalBlue, backgroundColor: '#EAF4FF', paddingHorizontal: 8, paddingVertical: 4, borderRadius: Radius.sm, overflow: 'hidden', fontWeight: '800' },
  buyBtn: { marginTop: Spacing.lg, backgroundColor: Brand.royalBlue, minHeight: 48, borderRadius: Radius.round, alignItems: 'center', justifyContent: 'center' },
  buyText: { ...Typography.bodyMed, color: Brand.white, fontWeight: '800' },
});
