import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import { useEffect, useRef, useState } from 'react';
import { ActivityIndicator, Alert, KeyboardAvoidingView, Platform, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { sendOtp } from '@/api/authApi';
import { Logo, PrimaryButton } from '@/components/wheelwash/ui';
import { BASE_URL } from '@/config/api';
import { BORDER, MUTED, PRIMARY, TEXT } from '@/lib/wheelwash-data';

export default function LoginScreen() {
  const [phone, setPhone] = useState('');
  const [loading, setLoading] = useState(false);
  const mountedRef = useRef(true);

  useEffect(() => {
    mountedRef.current = true;
    return () => {
      mountedRef.current = false;
    };
  }, []);

  const onSendOtp = async () => {
    const mobileNumber = phone.replace(/\D/g, '');
    if (mobileNumber.length !== 10) {
      Alert.alert('Invalid number', 'Please enter a valid 10 digit mobile number.');
      return;
    }

    setLoading(true);
    try {
      const result = await sendOtp(mobileNumber);
      router.push({ pathname: '/verify', params: { phone: mobileNumber, otp: result?.otp || '' } });
    } catch (err) {
      if (mountedRef.current) {
        Alert.alert('OTP failed', err instanceof Error ? err.message : 'Could not send OTP.');
      }
    } finally {
      if (mountedRef.current) setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.root}>
      <KeyboardAvoidingView style={styles.root} behavior={Platform.OS === 'ios' ? 'padding' : 'height'}>
        <ScrollView keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
          <View style={styles.brandBlock}>
            <Logo size="md" />
            <Text style={styles.title}>Welcome to{'\n'}<Text style={styles.titleBlue}>WheelWash</Text></Text>
            <Text style={styles.subtitle}>Book premium car care at your doorstep with real-time updates.</Text>
          </View>

          <View style={styles.card}>
            <Text style={styles.cardTitle}>Login with mobile</Text>
            <Text style={styles.cardSub}>We will send a 6 digit OTP to verify your account.</Text>

            <View style={styles.inputWrap}>
              <View style={styles.country}>
                <Text style={styles.flag}>IN</Text>
                <Text style={styles.prefix}>+91</Text>
                <Ionicons name="chevron-down" size={18} color={TEXT} />
              </View>
              <View style={styles.divider} />
              <TextInput
                style={[styles.input, Platform.OS === 'web' && ({ outlineStyle: 'none' } as any)]}
                keyboardType="number-pad"
                maxLength={10}
                placeholder="Mobile number"
                placeholderTextColor="#7A8497"
                value={phone}
                onChangeText={setPhone}
                editable={!loading}
                returnKeyType="done"
              />
            </View>

            <PrimaryButton title={loading ? 'Sending OTP...' : 'Send OTP'} icon="arrow-forward" onPress={loading ? undefined : onSendOtp} />
            {loading && <ActivityIndicator style={styles.loader} color={PRIMARY} />}

          </View>

          <View style={styles.terms}>
            <Ionicons name="lock-closed-outline" size={16} color={MUTED} />
            <Text style={styles.termsText}>By continuing, you agree to <Text style={styles.link}>Terms</Text> & <Text style={styles.link}>Privacy</Text></Text>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#F7FBFF' },
  content: { flexGrow: 1, paddingHorizontal: 22, paddingTop: 28, paddingBottom: 28, justifyContent: 'center' },
  brandBlock: { alignItems: 'center', marginBottom: 28 },
  title: { marginTop: 34, color: TEXT, fontSize: 38, lineHeight: 47, fontWeight: '900', textAlign: 'center', letterSpacing: 0 },
  titleBlue: { color: PRIMARY },
  subtitle: { marginTop: 16, color: '#566172', fontSize: 17, lineHeight: 25, textAlign: 'center', maxWidth: 330 },
  card: { backgroundColor: '#fff', borderRadius: 24, borderWidth: 1, borderColor: BORDER, padding: 20, shadowColor: '#0B4DA2', shadowOpacity: 0.1, shadowRadius: 18, shadowOffset: { width: 0, height: 8 }, elevation: 5 },
  cardTitle: { color: TEXT, fontSize: 24, fontWeight: '900' },
  cardSub: { color: MUTED, fontSize: 15, lineHeight: 22, marginTop: 8 },
  inputWrap: { marginTop: 22, marginBottom: 18, minHeight: 66, borderRadius: 18, borderWidth: 1.5, borderColor: '#D6E0EC', flexDirection: 'row', alignItems: 'center', paddingHorizontal: 16, backgroundColor: '#fff' },
  country: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  flag: { color: PRIMARY, fontSize: 15, fontWeight: '900' },
  prefix: { color: TEXT, fontSize: 20, fontWeight: '900' },
  divider: { height: 36, width: 1.5, backgroundColor: '#D3DCE8', marginHorizontal: 16 },
  input: { flex: 1, minWidth: 0, color: TEXT, fontSize: 22, fontWeight: '700' },
  loader: { marginTop: 12 },
  terms: { marginTop: 18, flexDirection: 'row', gap: 8, alignItems: 'center', justifyContent: 'center' },
  termsText: { color: '#566172', fontSize: 13, fontWeight: '500', textAlign: 'center' },
  link: { color: PRIMARY, fontWeight: '800' },
});
