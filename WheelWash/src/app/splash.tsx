import React, { useEffect } from 'react';
import { View, StyleSheet, ActivityIndicator, Text } from 'react-native';
import { useRouter } from 'expo-router';
import { Brand, Typography, Radius } from '@/constants/theme';
import { WashMateLogo } from '@/components/washmate/WashMateLogo';

export default function SplashScreen() {
  const router = useRouter();

  useEffect(() => {
    // Auto navigation to login screen after 2.5 seconds
    const timer = setTimeout(() => {
      router.push('/login');
    }, 2500);
    return () => clearTimeout(timer);
  }, [router]);

  return (
    <View style={styles.container}>
      {/* Background Accent */}
      <View style={styles.bgAccentTop} />
      <View style={styles.bgAccentBottom} />

      <View style={styles.content}>
        {/* We use a custom larger version of the logo directly here for exact matching */}
        <View style={styles.logoWrapper}>
          <View style={styles.icon}>
            <Text style={styles.iconEmoji}>💦</Text>
          </View>
          <Text style={styles.appName}>
            Wash<Text style={{ color: Brand.aqua }}>Mate</Text>
          </Text>
          <Text style={styles.tagline}>Premium doorstep car wash</Text>
        </View>
      </View>

      <View style={styles.footer}>
        <ActivityIndicator size="small" color={Brand.royalBlue} />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Brand.white,
    justifyContent: 'center',
    alignItems: 'center',
    overflow: 'hidden',
  },
  bgAccentTop: {
    position: 'absolute',
    top: -200,
    right: -100,
    width: 400,
    height: 400,
    borderRadius: 200,
    backgroundColor: Brand.aquaLight,
    opacity: 0.15,
  },
  bgAccentBottom: {
    position: 'absolute',
    bottom: -150,
    left: -150,
    width: 350,
    height: 350,
    borderRadius: 175,
    backgroundColor: Brand.royalBlue,
    opacity: 0.05,
  },
  content: {
    alignItems: 'center',
    justifyContent: 'center',
    flex: 1,
  },
  logoWrapper: {
    alignItems: 'center',
    gap: 8,
  },
  icon: {
    width: 80,
    height: 80,
    borderRadius: Radius.xxl,
    backgroundColor: Brand.surface,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  iconEmoji: {
    fontSize: 48,
  },
  appName: {
    ...Typography.hero,
    fontSize: 36,
    color: Brand.royalBlue,
    fontWeight: '900',
  },
  tagline: {
    ...Typography.bodyMed,
    color: Brand.textSecondary,
    letterSpacing: 0.5,
  },
  footer: {
    position: 'absolute',
    bottom: 50,
  },
});
