import React, { useEffect, useState } from 'react';
import { SafeScreen } from '../../components/SafeScreen';
import { View, Text, StyleSheet, TextInput, ScrollView, TouchableOpacity, Alert } from 'react-native';
import { createWorker, getWorkerDetail, updateWorker } from '../../api/adminApi';
import { LoadingView } from '../../components/LoadingView';

export const AdminWorkerFormScreen = ({ route, navigation }: any) => {
  const { id } = route.params || {};
  const isEdit = !!id;

  const [loading, setLoading] = useState(isEdit);
  const [saving, setSaving] = useState(false);

  const [formData, setFormData] = useState({
    name: '',
    email: '',
    mobile_number: '',
    password: '',
    skills: '', // Will split by comma for array
    service_area: '',
    current_status: 'available',
    location_lat: '',
    location_lng: ''
  });

  useEffect(() => {
    if (isEdit) {
      loadWorker();
    }
  }, [id]);

  const loadWorker = async () => {
    try {
      const res = await getWorkerDetail(id);
      const worker = res.data?.data || res.data;
      setFormData({
        name: worker.user?.name || '',
        email: worker.user?.email || '',
        mobile_number: worker.user?.mobile_number || '',
        password: '',
        skills: Array.isArray(worker.skills) ? worker.skills.join(', ') : (worker.skills || ''),
        service_area: worker.service_area || '',
        current_status: worker.current_status || 'available',
        location_lat: worker.latitude ? String(worker.latitude) : '',
        location_lng: worker.longitude ? String(worker.longitude) : ''
      });
    } catch (e) {
      Alert.alert('Error', 'Failed to load worker details');
      navigation.goBack();
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    if (!formData.name || !formData.mobile_number) {
      Alert.alert('Error', 'Name and mobile number are required');
      return;
    }

    setSaving(true);
    try {
      const payload = {
        ...formData,
        skills: formData.skills ? formData.skills.split(',').map((s: string) => s.trim()).filter(Boolean) : [],
        location_lat: formData.location_lat ? parseFloat(formData.location_lat) : null,
        location_lng: formData.location_lng ? parseFloat(formData.location_lng) : null,
      };

      if (isEdit) {
        const { password, ...updatePayload } = payload;
        await updateWorker(id, password ? payload : updatePayload);
        Alert.alert('Success', 'Worker updated successfully');
      } else {
        await createWorker(payload);
        Alert.alert('Success', 'Worker created successfully');
      }
      navigation.goBack();
    } catch (e: any) {
      const msg = e.response?.data?.message || e.message || 'Failed to save worker';
      Alert.alert('Error', msg);
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <LoadingView message="Loading..." />;

  return (
    <View style={styles.container}>
      <SafeScreen scrollable style={styles.content}>
      <Text style={styles.title}>{isEdit ? 'Edit Worker' : 'Add Worker'}</Text>

      <Text style={styles.label}>Name *</Text>
      <TextInput style={styles.input} value={formData.name} onChangeText={(t) => setFormData({ ...formData, name: t })} placeholder="Enter name" />

      <Text style={styles.label}>Mobile Number *</Text>
      <TextInput style={styles.input} value={formData.mobile_number} onChangeText={(t) => setFormData({ ...formData, mobile_number: t })} placeholder="Enter mobile" keyboardType="phone-pad" />

      <Text style={styles.label}>Email</Text>
      <TextInput style={styles.input} value={formData.email} onChangeText={(t) => setFormData({ ...formData, email: t })} placeholder="Enter email" keyboardType="email-address" autoCapitalize="none" />

      <Text style={styles.label}>{isEdit ? 'Password (leave blank to keep current)' : 'Password (optional, default: 12345678)'}</Text>
      <TextInput style={styles.input} value={formData.password} onChangeText={(t) => setFormData({ ...formData, password: t })} placeholder="Enter password" secureTextEntry />

      <Text style={styles.label}>Skills (comma separated)</Text>
      <TextInput style={styles.input} value={formData.skills} onChangeText={(t) => setFormData({ ...formData, skills: t })} placeholder="e.g. foam_wash, detailing" />

      <Text style={styles.label}>Service Area</Text>
      <TextInput style={styles.input} value={formData.service_area} onChangeText={(t) => setFormData({ ...formData, service_area: t })} placeholder="Enter service area" />

      <View style={styles.row}>
        <View style={styles.flex1}>
          <Text style={styles.label}>Latitude</Text>
          <TextInput style={styles.input} value={formData.location_lat} onChangeText={(t) => setFormData({ ...formData, location_lat: t })} placeholder="0.00000" keyboardType="numeric" />
        </View>
        <View style={styles.space} />
        <View style={styles.flex1}>
          <Text style={styles.label}>Longitude</Text>
          <TextInput style={styles.input} value={formData.location_lng} onChangeText={(t) => setFormData({ ...formData, location_lng: t })} placeholder="0.00000" keyboardType="numeric" />
        </View>
      </View>

      <View style={styles.switchRow}>
        <Text style={styles.label}>Status ({formData.current_status})</Text>
        <View style={styles.statusButtons}>
          {['available', 'busy', 'inactive'].map(status => (
            <TouchableOpacity 
              key={status} 
              style={[styles.statusBtn, formData.current_status === status && styles.statusBtnActive]}
              onPress={() => setFormData({ ...formData, current_status: status })}
            >
              <Text style={[styles.statusBtnText, formData.current_status === status && styles.statusBtnTextActive]}>{status}</Text>
            </TouchableOpacity>
          ))}
        </View>
      </View>

      <TouchableOpacity style={styles.saveBtn} onPress={handleSave} disabled={saving}>
        <Text style={styles.saveBtnText}>{saving ? 'Saving...' : 'Save Worker'}</Text>
      </TouchableOpacity>
      </SafeScreen>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA' },
  content: { padding: 16, paddingBottom: 40 },
  title: { fontSize: 24, fontWeight: 'bold', color: '#333', marginBottom: 20 },
  label: { fontSize: 14, fontWeight: 'bold', color: '#555', marginBottom: 8, marginTop: 12 },
  input: { backgroundColor: '#FFF', borderWidth: 1, borderColor: '#DDD', borderRadius: 8, paddingHorizontal: 12, height: 48, fontSize: 16 },
  row: { flexDirection: 'row' },
  flex1: { flex: 1 },
  space: { width: 12 },
  switchRow: { marginTop: 16 },
  statusButtons: { flexDirection: 'row', gap: 8, marginTop: 8 },
  statusBtn: { flex: 1, height: 40, alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: '#DDD', borderRadius: 8, backgroundColor: '#FFF' },
  statusBtnActive: { backgroundColor: '#007BFF', borderColor: '#007BFF' },
  statusBtnText: { color: '#555', fontWeight: 'bold', textTransform: 'capitalize' },
  statusBtnTextActive: { color: '#FFF' },
  saveBtn: { backgroundColor: '#28A745', height: 50, borderRadius: 8, alignItems: 'center', justifyContent: 'center', marginTop: 32 },
  saveBtnText: { color: '#FFF', fontSize: 18, fontWeight: 'bold' }
});
