import { Ionicons } from '@expo/vector-icons';
import { router, useLocalSearchParams } from 'expo-router';
import { useEffect, useRef, useState } from 'react';
import { ActivityIndicator, Alert, KeyboardAvoidingView, Platform, ScrollView, StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Logo, PrimaryButton } from '@/components/wheelwash/ui';
import { MUTED, PRIMARY, TEXT } from '@/lib/wheelwash-data';
import { getLocation } from '@/lib/wheelwash-storage';
import { useAuthStore } from '@/store/authStore';

export default function OtpScreen() {
  const { phone, otp: devOtp } = useLocalSearchParams<{ phone?: string; otp?: string }>();
  const [otp, setOtp] = useState(devOtp || '');
  const [loading, setLoading] = useState(false);
  const mountedRef = useRef(true);
  const { login } = useAuthStore();

  useEffect(() => {
    mountedRef.current = true;
    return () => {
      mountedRef.current = false;
    };
  }, []);

  const verify = async () => {
    if (!phone || otp.length !== 6) {
      Alert.alert('Invalid OTP', 'Please enter the 6 digit OTP.');
      return;
    }

    setLoading(true);
    try {
      await login(phone, otp);
      const location = await getLocation();
      router.replace(location ? '/(tabs)' : '/location');
    } catch (err) {
      if (mountedRef.current) {
        Alert.alert('Login failed', err instanceof Error ? err.message : 'Please try again.');
      }
    } finally {
      if (mountedRef.current) setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.root}>
      <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={styles.root}>
        <ScrollView keyboardShouldPersistTaps="handled" contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
          <View style={styles.header}>
            <TouchableOpacity style={styles.back} onPress={() => router.canGoBack() ? router.back() : router.replace('/')}>
              <Ionicons name="arrow-back" size={26} color={TEXT} />
            </TouchableOpacity>
            <Logo />
            <View style={styles.back} />
          </View>

          <View style={styles.card}>
            <View style={styles.icon}><Ionicons name="chatbubble-ellipses-outline" size={38} color={PRIMARY} /></View>
            <Text style={styles.title}>Verify OTP</Text>
            <Text style={styles.subtitle}>Enter the 6 digit code sent to +91 {phone || '9876543210'}.</Text>

            {devOtp ? (
              <View style={styles.devOtpBox}>
                <Text style={styles.devLabel}>Development OTP</Text>
                <Text style={styles.devOtp}>{devOtp}</Text>
              </View>
            ) : null}

            <TextInput
              style={styles.otp}
              keyboardType="number-pad"
              maxLength={6}
              value={otp}
              onChangeText={setOtp}
              placeholder="000000"
              placeholderTextColor="#B8C1CF"
              editable={!loading}
              returnKeyType="done"
            />
            <TouchableOpacity style={styles.button} onPress={loading ? undefined : verify} activeOpacity={0.8}>
              <Text style={styles.buttonText}>{loading ? 'Verifying...' : 'Verify & Continue'}</Text>
            </TouchableOpacity>
            {loading && <ActivityIndicator style={styles.loader} color={PRIMARY} />}
            <Text style={styles.resend}>Did not receive OTP? <Text style={styles.link}>Go back and resend</Text></Text>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#F7FBFF' },
  content: { flexGrow: 1, paddingHorizontal: 22, paddingTop: 16, paddingBottom: 28 },
  header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  back: { width: 44, height: 44, justifyContent: 'center' },
  card: { marginTop: 46, backgroundColor: '#fff', borderRadius: 24, padding: 22, alignItems: 'center', borderWidth: 1, borderColor: '#E3EAF3', shadowColor: '#0B4DA2', shadowOpacity: 0.1, shadowRadius: 18, shadowOffset: { width: 0, height: 8 }, elevation: 5 },
  icon: { width: 82, height: 82, borderRadius: 24, backgroundColor: '#EAF4FF', alignItems: 'center', justifyContent: 'center' },
  title: { marginTop: 24, color: TEXT, fontSize: 32, fontWeight: '900' },
  subtitle: { marginTop: 10, color: MUTED, fontSize: 16, lineHeight: 24, textAlign: 'center' },
  devOtpBox: { width: '100%', marginTop: 20, borderRadius: 16, backgroundColor: '#EAF4FF', padding: 14, alignItems: 'center' },
  devLabel: { color: MUTED, fontSize: 12, fontWeight: '800', textTransform: 'uppercase' },
  devOtp: { marginTop: 4, color: PRIMARY, fontSize: 30, fontWeight: '900', letterSpacing: 8 },
  otp: { marginTop: 24, marginBottom: 18, width: '100%', minHeight: 68, borderRadius: 18, borderWidth: 1.5, borderColor: '#D6E0EC', color: TEXT, fontSize: 28, fontWeight: '900', letterSpacing: 10, textAlign: 'center', backgroundColor: '#fff' },
  button: {
    width: '100%',
    height: 60,
    borderRadius: 18,
    backgroundColor: '#1177F2',
    alignItems: 'center',
    justifyContent: 'center',
  },
  buttonText: {
    color: '#FFFFFF',
    fontSize: 20,
    fontWeight: '800',
    textAlign: 'center',
  },
  loader: { marginTop: 12 },
  resend: { marginTop: 20, color: MUTED, fontSize: 15, textAlign: 'center' },
  link: { color: PRIMARY, fontWeight: '800' },
});
