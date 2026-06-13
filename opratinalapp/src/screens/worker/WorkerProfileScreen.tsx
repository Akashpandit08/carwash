import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { getWorkerProfile } from '../../api/workerApi';
import { LoadingView } from '../../components/LoadingView';

export const WorkerProfileScreen = () => {
  const [profile, setProfile] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getWorkerProfile().then((res) => setProfile(res.data?.data || res.data)).finally(() => setLoading(false));
  }, []);

  if (loading) return <LoadingView message="Loading profile..." />;

  const opsProfile = profile?.profile || profile?.worker_profile || profile?.workerProfile || {};

  return (
    <ScrollView style={styles.container}>
      <View style={styles.card}>
        <Text style={styles.title}>{profile?.name || 'Worker'}</Text>
        <Text style={styles.line}>Phone: {profile?.mobile_number || 'N/A'}</Text>
        <Text style={styles.line}>Status: {opsProfile.current_status || profile?.status || 'N/A'}</Text>
        <Text style={styles.line}>Rating: {opsProfile.rating || 'N/A'}</Text>
        <Text style={styles.line}>Total Jobs: {opsProfile.total_jobs || 0}</Text>
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  card: { backgroundColor: '#FFFFFF', padding: 16, borderRadius: 8 },
  title: { fontSize: 22, fontWeight: '800', marginBottom: 12, color: '#111827' },
  line: { fontSize: 15, color: '#475569', marginBottom: 8 },
});
