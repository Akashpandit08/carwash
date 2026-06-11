import { Ionicons } from '@expo/vector-icons';
import { router, useFocusEffect } from 'expo-router';
import { useCallback, useState } from 'react';
import { ActivityIndicator, Alert, KeyboardAvoidingView, Platform, ScrollView, StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { BORDER, MUTED, PRIMARY, TEXT, Vehicle } from '@/lib/wheelwash-data';
import { Card, Logo, PrimaryButton, SelectedBadge } from '@/components/wheelwash/ui';
import { useVehicleStore } from '@/store/vehicleStore';

export default function AddVehicleScreen() {
  const [current, setCurrent] = useState<Vehicle | null>(null);
  const [type, setType] = useState('car');
  const [brand, setBrand] = useState('Hyundai');
  const [model, setModel] = useState('i20');
  const [registrationNumber, setRegistrationNumber] = useState('UP80AB1234');
  const [fuelType, setFuelType] = useState('petrol');
  const { selectedVehicle, createVehicle, saveExistingVehicle, loading, error } = useVehicleStore();

  useFocusEffect(
    useCallback(() => {
      if (selectedVehicle) {
        setCurrent(selectedVehicle);
        setType(selectedVehicle.type || 'car');
        setBrand(selectedVehicle.brand || '');
        setModel(selectedVehicle.model || '');
        setRegistrationNumber(selectedVehicle.registrationNumber || '');
        setFuelType(selectedVehicle.color || 'petrol');
      }
    }, [selectedVehicle]),
  );

  const onSave = async () => {
    try {
      const payload = {
        vehicle_type: type.toLowerCase(),
        brand,
        model,
        number: registrationNumber,
        fuel_type: fuelType,
        color: fuelType,
      };
      if (current?.id) {
        await saveExistingVehicle(current.id, payload);
      } else {
        await createVehicle(payload);
      }
      router.replace('/(tabs)');
    } catch (err) {
      Alert.alert('Vehicle save failed', err instanceof Error ? err.message : 'Please try again.');
    }
  };

  return (
    <SafeAreaView style={styles.root} edges={['top']}>
      <KeyboardAvoidingView style={styles.root} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        <View style={styles.header}>
          <TouchableOpacity style={styles.headerIcon} onPress={() => router.back()}>
            <Ionicons name="arrow-back" size={26} color={TEXT} />
          </TouchableOpacity>
          <Logo />
          <TouchableOpacity style={styles.headerIcon}>
            <Ionicons name="notifications-outline" size={24} color={TEXT} />
            <View style={styles.dot} />
          </TouchableOpacity>
        </View>

        <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
          <Text style={styles.title}>My Vehicles</Text>
          <Text style={styles.subtitle}>Manage your vehicles</Text>

          {current && (
            <Card style={styles.current}>
              <View style={styles.vehicleThumb}><Ionicons name="car-sport" size={32} color={PRIMARY} /></View>
              <View style={{ flex: 1 }}>
                <Text style={styles.labelBlue}>Your Vehicle</Text>
                <Text style={styles.currentName}>{current.brand} {current.model} - {current.registrationNumber}</Text>
              </View>
              <SelectedBadge />
              <Ionicons name="chevron-down" size={24} color={TEXT} />
            </Card>
          )}

          <Card style={styles.formCard}>
            <View style={styles.formHead}>
              <View style={styles.addIcon}>
                <Ionicons name="car-sport" size={34} color={PRIMARY} />
              </View>
              <View>
                <Text style={styles.formTitle}>Add New Vehicle</Text>
                <Text style={styles.subtitle}>Enter your vehicle details</Text>
              </View>
            </View>
            {error && <Text style={styles.errorText}>{error}</Text>}
            <Field label="Vehicle Type" icon="car-outline" value={type} onChangeText={setType} placeholder="car, bike, suv, truck" />
            <Field label="Brand" icon="business-outline" value={brand} onChangeText={setBrand} placeholder="Select Brand" />
            <Field label="Model" icon="car-sport-outline" value={model} onChangeText={setModel} placeholder="Select Model" />
            <Field label="Registration Number" icon="keypad-outline" value={registrationNumber} onChangeText={setRegistrationNumber} placeholder="Enter Registration Number" />
            <Field label="Fuel Type" icon="water-outline" value={fuelType} onChangeText={setFuelType} placeholder="petrol" />
            <PrimaryButton title={loading ? 'Saving Vehicle...' : 'Save Vehicle'} onPress={onSave} />
            {loading && <ActivityIndicator color={PRIMARY} style={{ marginTop: 12 }} />}
          </Card>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

function Field(props: {
  label: string;
  icon: keyof typeof Ionicons.glyphMap;
  value: string;
  onChangeText: (value: string) => void;
  placeholder: string;
}) {
  return (
    <View style={styles.fieldBlock}>
      <Text style={styles.fieldLabel}>{props.label}</Text>
      <View style={styles.field}>
        <Ionicons name={props.icon} size={22} color={MUTED} />
        <TextInput
          style={styles.input}
          value={props.value}
          onChangeText={props.onChangeText}
          placeholder={props.placeholder}
          placeholderTextColor="#8A94A6"
        />
        <Ionicons name="chevron-down" size={20} color={MUTED} />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  header: { minHeight: 64, paddingHorizontal: 22, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  headerIcon: { width: 42, height: 42, alignItems: 'center', justifyContent: 'center' },
  dot: { position: 'absolute', top: 8, right: 7, width: 9, height: 9, borderRadius: 6, backgroundColor: '#FF3B30' },
  content: { paddingHorizontal: 22, paddingBottom: 28 },
  title: { color: TEXT, fontSize: 31, fontWeight: '900', marginTop: 8 },
  subtitle: { color: MUTED, fontSize: 17, marginTop: 6 },
  current: { marginTop: 28, padding: 14, flexDirection: 'row', alignItems: 'center', gap: 14 },
  vehicleThumb: { width: 94, height: 68, borderRadius: 12, backgroundColor: '#E8F3FF', alignItems: 'center', justifyContent: 'center' },
  labelBlue: { color: PRIMARY, fontSize: 15, fontWeight: '800', marginBottom: 8 },
  currentName: { color: TEXT, fontSize: 18, fontWeight: '900' },
  formCard: { marginTop: 26, padding: 24 },
  formHead: { flexDirection: 'row', alignItems: 'center', gap: 16, marginBottom: 26 },
  addIcon: { width: 70, height: 70, borderRadius: 28, backgroundColor: '#E7F2FF', alignItems: 'center', justifyContent: 'center' },
  formTitle: { color: TEXT, fontSize: 26, fontWeight: '900' },
  fieldBlock: { marginBottom: 18 },
  fieldLabel: { color: TEXT, fontSize: 16, fontWeight: '800', marginBottom: 10 },
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
  errorText: { color: '#B42318', fontSize: 14, fontWeight: '700', marginBottom: 12 },
});
