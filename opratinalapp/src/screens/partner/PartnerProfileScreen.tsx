import React, { useEffect, useState } from 'react';
import { ScrollView, View, Text, StyleSheet, Switch, Alert } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import apiClient from '../../api/client';
import { AppButton } from '../../components/AppButton';

export const PartnerProfileScreen = ({ navigation }: any) => {
  const [user, setUser] = useState<any>(null);
  const [isOnline, setIsOnline] = useState(false);
  const [updating, setUpdating] = useState(false);

  useEffect(() => {
    const loadUser = async () => {
      const userDataStr = await AsyncStorage.getItem('userData');
      if (userDataStr) {
        const userData = JSON.parse(userDataStr);
        setUser(userData);
        // We'll default to online true if not set, or fetch from real profile
        setIsOnline(userData.current_status === 'online' || true);
      }
    };
    loadUser();
  }, []);

  const toggleOnlineStatus = async (value: boolean) => {
    setIsOnline(value);
    setUpdating(true);
    try {
      await apiClient.post('/app/online-status', { is_online: value });
    } catch (e) {
      setIsOnline(!value);
      Alert.alert('Error', 'Failed to update online status');
    } finally {
      setUpdating(false);
    }
  };

  const handleLogout = async () => {
    Alert.alert('Logout', 'Are you sure you want to log out?', [
      { text: 'Cancel', style: 'cancel' },
      { 
        text: 'Logout', 
        style: 'destructive',
        onPress: async () => {
          await AsyncStorage.clear();
          navigation.reset({ index: 0, routes: [{ name: 'Auth' }] });
        }
      }
    ]);
  };

  if (!user) return <View style={styles.container} />;

  return (
    <ScrollView style={styles.container}>
      <View style={styles.card}>
        <View style={styles.avatarPlaceholder}>
          <Text style={styles.avatarText}>{user.name?.charAt(0)}</Text>
        </View>
        <Text style={styles.name}>{user.name}</Text>
        <Text style={styles.phone}>{user.mobile_number}</Text>
      </View>

      <View style={styles.card}>
        <View style={styles.row}>
          <View>
            <Text style={styles.settingTitle}>Online Status</Text>
            <Text style={styles.settingDesc}>
              {isOnline ? 'You are receiving new bookings' : 'You are currently offline'}
            </Text>
          </View>
          <Switch 
            value={isOnline} 
            onValueChange={toggleOnlineStatus} 
            disabled={updating}
            trackColor={{ false: '#767577', true: '#81b0ff' }}
            thumbColor={isOnline ? '#007BFF' : '#f4f3f4'}
          />
        </View>
      </View>

      <AppButton title="Logout" type="secondary" onPress={handleLogout} style={styles.logoutBtn} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  card: { backgroundColor: '#FFF', padding: 20, borderRadius: 12, marginBottom: 16, elevation: 1, alignItems: 'center' },
  avatarPlaceholder: { width: 80, height: 80, borderRadius: 40, backgroundColor: '#007BFF', justifyContent: 'center', alignItems: 'center', marginBottom: 12 },
  avatarText: { fontSize: 36, color: '#FFF', fontWeight: 'bold' },
  name: { fontSize: 22, fontWeight: 'bold', color: '#333', marginBottom: 4 },
  phone: { fontSize: 16, color: '#666' },
  row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', width: '100%' },
  settingTitle: { fontSize: 16, fontWeight: '600', color: '#333' },
  settingDesc: { fontSize: 13, color: '#888', marginTop: 4 },
  logoutBtn: { marginTop: 24, backgroundColor: '#FF3B30' },
});
