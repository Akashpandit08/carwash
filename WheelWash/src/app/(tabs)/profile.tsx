import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import { ScrollView, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { BORDER, MUTED, PRIMARY, TEXT } from '@/lib/wheelwash-data';
import { Card, ScreenHeader } from '@/components/wheelwash/ui';
import { useAuthStore } from '@/store/authStore';

const rows: {
  title: string;
  subtitle: string;
  icon: keyof typeof Ionicons.glyphMap;
  color: string;
  route?: '/add-vehicle';
}[] = [
  { title: 'My Vehicles', subtitle: 'Manage your vehicles', icon: 'car-sport', color: PRIMARY, route: '/add-vehicle' },
  { title: 'Saved Addresses', subtitle: 'Manage your addresses', icon: 'location', color: '#16B981' },
  { title: 'Offers & Coupons', subtitle: 'View exclusive offers', icon: 'pricetag', color: '#8B5CF6' },
  { title: 'Payment Methods', subtitle: 'Manage payment options', icon: 'card', color: '#F59E0B' },
  { title: 'Help & Support', subtitle: 'Get help and support', icon: 'headset', color: PRIMARY },
  { title: 'About Us', subtitle: 'Know more about WheelWash', icon: 'information-circle', color: '#6D5BD0' },
];

export default function ProfileTab() {
  const { user, logout } = useAuthStore();

  const onLogout = async () => {
    await logout();
    router.replace('/login');
  };

  return (
    <SafeAreaView style={styles.root} edges={['top']}>
      <ScreenHeader />
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        <Text style={styles.title}>Profile</Text>
        <Text style={styles.subtitle}>Manage your account and preferences</Text>

        <Card style={styles.userCard}>
          <View style={styles.avatar}><Ionicons name="person" size={58} color={PRIMARY} /></View>
          <View style={{ flex: 1 }}>
            <Text style={styles.userName}>{user?.name || 'Customer'}</Text>
            <Text style={styles.phone}>{user?.mobile_number || user?.phone || 'Verified customer'}</Text>
            <View style={styles.verified}>
              <Ionicons name="shield-checkmark" size={18} color={PRIMARY} />
              <Text style={styles.verifiedText}>Verified</Text>
            </View>
          </View>
          <Ionicons name="chevron-forward" size={28} color={TEXT} />
        </Card>

        <Card style={styles.menu}>
          {rows.map((row, index) => (
            <TouchableOpacity
              key={row.title}
              style={[styles.row, index > 0 && styles.rowBorder]}
              onPress={() => row.route && router.push(row.route)}
              activeOpacity={0.8}
            >
              <View style={[styles.rowIcon, { backgroundColor: `${row.color}18` }]}>
                <Ionicons name={row.icon} size={28} color={row.color} />
              </View>
              <View style={{ flex: 1 }}>
                <Text style={styles.rowTitle}>{row.title}</Text>
                <Text style={styles.rowSub}>{row.subtitle}</Text>
              </View>
              <Ionicons name="chevron-forward" size={25} color={TEXT} />
            </TouchableOpacity>
          ))}
        </Card>

        <TouchableOpacity onPress={onLogout} activeOpacity={0.85}>
        <Card style={styles.logout}>
          <View style={[styles.rowIcon, { backgroundColor: '#FEECEC' }]}>
            <Ionicons name="log-out-outline" size={29} color="#DC2626" />
          </View>
          <View style={{ flex: 1 }}>
            <Text style={[styles.rowTitle, { color: '#DC2626' }]}>Logout</Text>
            <Text style={styles.rowSub}>Sign out of your account</Text>
          </View>
          <Ionicons name="chevron-forward" size={25} color={TEXT} />
        </Card>
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  content: { paddingHorizontal: 22, paddingBottom: 28 },
  title: { color: TEXT, fontSize: 34, fontWeight: '900', marginTop: 10 },
  subtitle: { color: MUTED, fontSize: 18, marginTop: 8 },
  userCard: { marginTop: 26, padding: 22, flexDirection: 'row', alignItems: 'center', gap: 20 },
  avatar: { width: 118, height: 118, borderRadius: 59, backgroundColor: '#DDEEFF', alignItems: 'center', justifyContent: 'center' },
  userName: { color: TEXT, fontSize: 30, fontWeight: '900' },
  phone: { color: MUTED, fontSize: 20, marginTop: 8 },
  verified: { marginTop: 12, alignSelf: 'flex-start', flexDirection: 'row', gap: 8, alignItems: 'center', backgroundColor: '#EAF4FF', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10 },
  verifiedText: { color: PRIMARY, fontSize: 16, fontWeight: '800' },
  menu: { marginTop: 24, overflow: 'hidden' },
  row: { minHeight: 104, paddingHorizontal: 20, flexDirection: 'row', alignItems: 'center', gap: 20 },
  rowBorder: { borderTopWidth: 1, borderTopColor: BORDER },
  rowIcon: { width: 64, height: 64, borderRadius: 18, alignItems: 'center', justifyContent: 'center' },
  rowTitle: { color: TEXT, fontSize: 21, fontWeight: '900' },
  rowSub: { color: MUTED, fontSize: 16, marginTop: 5 },
  logout: { marginTop: 24, minHeight: 100, paddingHorizontal: 20, flexDirection: 'row', alignItems: 'center', gap: 20 },
});
