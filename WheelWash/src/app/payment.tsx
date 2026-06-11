import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import { ScrollView, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { BORDER, MUTED, PRIMARY, TEXT } from '@/lib/wheelwash-data';
import { Card, PrimaryButton } from '@/components/wheelwash/ui';

export default function PaymentScreen() {
  const methods = [
    ['UPI', 'Pay using any UPI app', 'paper-plane-outline'],
    ['Credit / Debit Card', 'Pay using Visa, Mastercard, RuPay', 'card-outline'],
    ['Net Banking', 'Pay using your preferred bank', 'business-outline'],
    ['Cash on Delivery', 'Pay at your doorstep', 'cash-outline'],
    ['Wallet', 'Pay using your wallet balance', 'wallet-outline'],
  ] as const;

  return (
    <SafeAreaView style={styles.root} edges={['top']}>
      <View style={styles.header}>
        <TouchableOpacity style={styles.back} onPress={() => router.back()}><Ionicons name="arrow-back" size={26} color={TEXT} /></TouchableOpacity>
        <Text style={styles.headerTitle}>Payment</Text>
        <View style={styles.back} />
      </View>
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <Card style={styles.amount}>
          <View>
            <Text style={styles.amountLabel}>Order Amount</Text>
            <Text style={styles.amountText}>₹498</Text>
          </View>
          <Ionicons name="shield-checkmark" size={72} color={PRIMARY} />
        </Card>
        <Text style={styles.section}>Choose a payment method</Text>
        {methods.map((method, index) => (
          <Card key={method[0]} style={[styles.method, index === 1 && styles.methodActive]}>
            <View style={styles.methodIcon}><Ionicons name={method[2]} size={28} color={PRIMARY} /></View>
            <View style={{ flex: 1 }}>
              <Text style={styles.methodTitle}>{method[0]}</Text>
              <Text style={styles.methodSub}>{method[1]}</Text>
            </View>
            <Ionicons name={index === 1 ? 'radio-button-on' : 'radio-button-off'} size={32} color={index === 1 ? PRIMARY : '#A8AFBA'} />
            <Ionicons name="chevron-forward" size={25} color={TEXT} />
          </Card>
        ))}
        <Card style={styles.secure}>
          <View style={styles.lock}><Ionicons name="lock-closed" size={30} color={PRIMARY} /></View>
          <View style={{ flex: 1 }}>
            <Text style={styles.methodTitle}>100% Secure Payments</Text>
            <Text style={styles.methodSub}>Your payment details are safe with us and encrypted using industry-standard security.</Text>
          </View>
        </Card>
        <PrimaryButton title="Pay Now" icon="shield-checkmark" onPress={() => router.replace('/success')} />
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  header: { height: 64, paddingHorizontal: 22, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  back: { width: 42, height: 42, alignItems: 'center', justifyContent: 'center' },
  headerTitle: { color: PRIMARY, fontSize: 26, fontWeight: '900' },
  content: { padding: 22, gap: 16, paddingBottom: 28 },
  amount: { padding: 24, minHeight: 150, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  amountLabel: { color: MUTED, fontSize: 19 },
  amountText: { color: TEXT, fontSize: 54, fontWeight: '900', marginTop: 14 },
  section: { color: TEXT, fontSize: 22, fontWeight: '900', marginTop: 18, marginBottom: 4 },
  method: { padding: 18, flexDirection: 'row', alignItems: 'center', gap: 16 },
  methodActive: { borderColor: PRIMARY, borderWidth: 2 },
  methodIcon: { width: 62, height: 62, borderRadius: 16, backgroundColor: '#EAF4FF', alignItems: 'center', justifyContent: 'center' },
  methodTitle: { color: TEXT, fontSize: 21, fontWeight: '900' },
  methodSub: { color: MUTED, fontSize: 16, lineHeight: 23, marginTop: 6 },
  secure: { padding: 20, flexDirection: 'row', alignItems: 'center', gap: 18, backgroundColor: '#F3F9FF' },
  lock: { width: 70, height: 70, borderRadius: 35, backgroundColor: '#DDEEFF', alignItems: 'center', justifyContent: 'center' },
});
