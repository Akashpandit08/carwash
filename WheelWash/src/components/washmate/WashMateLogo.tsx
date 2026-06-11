import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { Brand, Typography } from '@/constants/theme';

interface Props {
  size?: 'sm' | 'md' | 'lg';
  dark?: boolean; // true = white text (for use on dark/gradient backgrounds)
}

export function WashMateLogo({ size = 'md', dark = false }: Props) {
  const scales = { sm: 0.75, md: 1, lg: 1.3 };
  const scale = scales[size];

  const textColor = dark ? Brand.white : Brand.royalBlue;
  const accentColor = dark ? Brand.aquaLight : Brand.aqua;

  return (
    <View style={styles.row}>
      {/* Water drop icon */}
      <View style={[styles.icon, { width: 32 * scale, height: 32 * scale, borderRadius: 10 * scale, backgroundColor: dark ? 'rgba(255,255,255,0.2)' : Brand.surface }]}>
        <Text style={{ fontSize: 17 * scale }}>💧</Text>
      </View>
      <View style={styles.textWrap}>
        <Text style={[styles.brand, { fontSize: 20 * scale, color: textColor }]}>
          Wash<Text style={{ color: accentColor }}>Mate</Text>
        </Text>
        <Text style={[styles.tagline, { fontSize: 9 * scale, color: dark ? Brand.textOnDarkSub : Brand.textMuted, letterSpacing: 1 }]}>
          PREMIUM DOORSTEP CAR WASH
        </Text>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  icon: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  textWrap: {
    gap: 1,
  },
  brand: {
    ...Typography.h2,
    fontWeight: '800',
    letterSpacing: -0.5,
  },
  tagline: {
    fontWeight: '600',
  },
});
