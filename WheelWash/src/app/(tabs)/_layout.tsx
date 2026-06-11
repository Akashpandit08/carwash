import { Tabs } from 'expo-router';
import { BottomNav, TabName } from '@/components/washmate/BottomNav';

const TAB_MAP: Record<string, TabName> = {
  index: 'home',
  bookings: 'bookings',
  profile: 'profile',
};

export default function TabsLayout() {
  return (
    <Tabs
      screenOptions={{
        headerShown: false,
      }}
      tabBar={({ state }) => {
        const routeName = state.routeNames[state.index];
        return <BottomNav active={TAB_MAP[routeName] || 'home'} />;
      }}
    >
      <Tabs.Screen name="index" options={{ title: 'Home' }} />
      <Tabs.Screen name="bookings" options={{ title: 'Bookings' }} />
      <Tabs.Screen name="offers" options={{ title: 'Offers', href: null }} />
      <Tabs.Screen name="profile" options={{ title: 'Profile' }} />
    </Tabs>
  );
}
