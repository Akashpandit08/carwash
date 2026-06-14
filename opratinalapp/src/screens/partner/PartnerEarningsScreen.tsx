import React, { useEffect, useState } from 'react';
import { View, FlatList, Text, StyleSheet, RefreshControl, Modal, TextInput, Alert, TouchableOpacity } from 'react-native';
import apiClient from '../../api/client';
import { LoadingView } from '../../components/LoadingView';
import { EmptyState } from '../../components/EmptyState';
import { AppButton } from '../../components/AppButton';

export const PartnerEarningsScreen = () => {
  const [data, setData] = useState<any>(null);
  const [ledger, setLedger] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  
  const [showPayoutModal, setShowPayoutModal] = useState(false);
  const [payoutAmount, setPayoutAmount] = useState('');

  const fetchEarnings = async () => {
    try {
      const res = await apiClient.get('/partner/earnings');
      setData(res.data?.data || {});
      
      const ledgerRes = await apiClient.get('/partner/ledger');
      setLedger(ledgerRes.data?.data || []);
    } catch (e) {
      console.log('Failed to fetch earnings');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchEarnings();
  }, []);

  const handleRequestPayout = async () => {
    if (!payoutAmount || isNaN(Number(payoutAmount))) {
      Alert.alert('Error', 'Enter a valid amount');
      return;
    }
    
    try {
      await apiClient.post('/partner/payout-request', { amount: Number(payoutAmount) });
      Alert.alert('Success', 'Payout request submitted');
      setShowPayoutModal(false);
      setPayoutAmount('');
      fetchEarnings();
    } catch (e: any) {
      Alert.alert('Error', e.response?.data?.message || 'Payout request failed');
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    fetchEarnings();
  };

  if (loading) return <LoadingView message="Loading Earnings..." />;

  return (
    <View style={styles.container}>
      <View style={styles.headerCard}>
        <Text style={styles.totalLabel}>Total Earnings</Text>
        <Text style={styles.totalValue}>₹{data?.total_earnings || '0.00'}</Text>
        
        <View style={styles.statsRow}>
          <View style={styles.statBox}>
            <Text style={styles.statLabel}>Today</Text>
            <Text style={styles.statValue}>₹{data?.today_earnings || '0'}</Text>
          </View>
          <View style={styles.statBox}>
            <Text style={styles.statLabel}>Pending Payout</Text>
            <Text style={[styles.statValue, { color: '#FF9800' }]}>₹{data?.pending_payout || '0'}</Text>
          </View>
        </View>
        
        <AppButton title="Request Payout" onPress={() => setShowPayoutModal(true)} style={{ marginTop: 16 }} />
      </View>

      <Text style={styles.sectionTitle}>Ledger History</Text>
      <FlatList
        data={ledger}
        keyExtractor={(item) => item.id?.toString()}
        renderItem={({ item }) => (
          <View style={styles.ledgerCard}>
            <View style={styles.row}>
              <Text style={styles.ledgerTitle}>Booking #{item.booking_id}</Text>
              <Text style={styles.ledgerAmount}>+ ₹{item.amount}</Text>
            </View>
            <Text style={styles.ledgerDate}>{new Date(item.date).toLocaleDateString('en-IN', { timeZone: 'Asia/Kolkata' })}</Text>
            <View style={styles.row}>
              <Text style={styles.ledgerDetail}>Mode: {item.payment_mode}</Text>
              <Text style={styles.ledgerDetail}>Comm: ₹{item.commission}</Text>
            </View>
          </View>
        )}
        ListEmptyComponent={<EmptyState title="No transactions yet" />}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
      />

      <Modal visible={showPayoutModal} transparent animationType="slide">
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>Request Payout</Text>
            <Text style={styles.modalSub}>Max available: ₹{data?.total_earnings - (data?.paid_payout || 0) - (data?.pending_payout || 0)}</Text>
            <TextInput 
              style={styles.input} 
              placeholder="Amount to withdraw" 
              keyboardType="numeric" 
              value={payoutAmount} 
              onChangeText={setPayoutAmount} 
            />
            <AppButton title="Submit Request" onPress={handleRequestPayout} />
            <AppButton title="Cancel" type="secondary" onPress={() => setShowPayoutModal(false)} style={{ marginTop: 8 }} />
          </View>
        </View>
      </Modal>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA', padding: 16 },
  headerCard: { backgroundColor: '#007BFF', padding: 20, borderRadius: 12, marginBottom: 20 },
  totalLabel: { color: 'rgba(255,255,255,0.8)', fontSize: 16 },
  totalValue: { color: '#FFF', fontSize: 36, fontWeight: 'bold', marginVertical: 8 },
  statsRow: { flexDirection: 'row', justifyContent: 'space-between', marginTop: 12 },
  statBox: { flex: 1, backgroundColor: 'rgba(255,255,255,0.2)', padding: 12, borderRadius: 8, marginRight: 8 },
  statLabel: { color: 'rgba(255,255,255,0.8)', fontSize: 12 },
  statValue: { color: '#FFF', fontSize: 18, fontWeight: 'bold', marginTop: 4 },
  sectionTitle: { fontSize: 18, fontWeight: 'bold', color: '#333', marginBottom: 12 },
  ledgerCard: { backgroundColor: '#FFF', padding: 16, borderRadius: 8, marginBottom: 12, elevation: 1 },
  row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  ledgerTitle: { fontSize: 16, fontWeight: '600', color: '#333' },
  ledgerAmount: { fontSize: 16, fontWeight: 'bold', color: '#28A745' },
  ledgerDate: { fontSize: 12, color: '#999', marginVertical: 4 },
  ledgerDetail: { fontSize: 13, color: '#666' },
  modalContainer: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', padding: 20 },
  modalContent: { backgroundColor: '#FFF', padding: 20, borderRadius: 12 },
  modalTitle: { fontSize: 20, fontWeight: 'bold', marginBottom: 4 },
  modalSub: { fontSize: 14, color: '#666', marginBottom: 16 },
  input: { borderWidth: 1, borderColor: '#DDD', padding: 12, borderRadius: 8, marginBottom: 16, fontSize: 16 },
});
