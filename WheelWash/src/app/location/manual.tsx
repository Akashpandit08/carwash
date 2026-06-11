import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import { useState } from 'react';
import { StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { BORDER, MUTED, PRIMARY, TEXT } from '@/lib/wheelwash-data';
import { Logo, PrimaryButton } from '@/components/wheelwash/ui';
import { useLocationStore } from '@/store/locationStore';

export default function ManualLocationScreen() {
  const [city, setCity] = useState('Agra');
  const [area, setArea] = useState('Dayal Bagh');
  const [pincode, setPincode] = useState('282005');
  const { saveLocation } = useLocationStore();

  const save = async () => {
    await saveLocation({
      city,
      area,
      pincode,
      region: 'UP',
      fullAddress: `${area}, ${city}, Uttar Pradesh - ${pincode}`,
    });
    router.replace('/(tabs)');
  };

  return (
    <SafeAreaView style={styles.root}>
      <View style={styles.top}>
        <TouchableOpacity style={styles.back} onPress={() => router.back()}>
          <Ionicons name="arrow-back" size={26} color={TEXT} />
        </TouchableOpacity>
        <Logo />
        <View style={styles.back} />
      </View>
      <View style={styles.content}>
        <Text style={styles.title}>Enter Service Address</Text>
        <Text style={styles.subtitle}>Tell us where we should bring the doorstep car wash.</Text>
        <Field label="City" value={city} onChangeText={setCity} icon="business-outline" />
        <Field label="Area" value={area} onChangeText={setArea} icon="location-outline" />
        <Field label="Pincode" value={pincode} onChangeText={setPincode} icon="keypad-outline" keyboardType="number-pad" />
        <PrimaryButton title="Save Address" icon="checkmark-circle-outline" onPress={save} />
      </View>
    </SafeAreaView>
  );
}

function Field(props: {
  label: string;
  value: string;
  onChangeText: (text: string) => void;
  icon: keyof typeof Ionicons.glyphMap;
  keyboardType?: 'default' | 'number-pad';
}) {
  return (
    <View style={styles.fieldBlock}>
      <Text style={styles.label}>{props.label}</Text>
      <View style={styles.field}>
        <Ionicons name={props.icon} size={23} color={MUTED} />
        <TextInput
          style={styles.input}
          value={props.value}
          onChangeText={props.onChangeText}
          keyboardType={props.keyboardType}
          placeholderTextColor="#8A94A6"
        />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  top: { paddingHorizontal: 20, paddingTop: 14, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  back: { width: 44, height: 44, alignItems: 'center', justifyContent: 'center' },
  content: { flex: 1, padding: 24 },
  title: { color: TEXT, fontSize: 32, fontWeight: '900', marginTop: 26 },
  subtitle: { color: MUTED, fontSize: 17, lineHeight: 25, marginTop: 8, marginBottom: 26 },
  fieldBlock: { marginBottom: 18 },
  label: { color: TEXT, fontSize: 16, fontWeight: '800', marginBottom: 10 },
  field: {
    minHeight: 64,
    borderRadius: 16,
    borderWidth: 1.4,
    borderColor: BORDER,
    paddingHorizontal: 16,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 14,
  },
  input: { flex: 1, color: TEXT, fontSize: 18, fontWeight: '600' },
});
