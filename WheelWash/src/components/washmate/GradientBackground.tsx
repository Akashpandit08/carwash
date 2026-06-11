import React from 'react';
import { View, StyleSheet, ViewStyle } from 'react-native';
import { Brand } from '@/constants/theme';

interface Props {
  colors?: string[];
  style?: ViewStyle;
  children?: React.ReactNode;
}

/**
 * Simulates a gradient using layered views (no expo-linear-gradient dependency needed).
 * For true gradients, swap the inner View with LinearGradient if available.
 */
export function GradientBackground({ colors = Brand.gradientHero, style, children }: Props) {
  return (
    <View style={[styles.container, { backgroundColor: colors[0] }, style]}>
      {/* Subtle diagonal overlay to simulate gradient */}
      <View style={[styles.overlay, { backgroundColor: colors[1] }]} pointerEvents="none" />
      {children}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    overflow: 'hidden',
  },
  overlay: {
    ...StyleSheet.absoluteFill,
    opacity: 0.55,
    transform: [{ skewY: '-12deg' }, { translateY: -60 }, { translateX: 80 }],
  },
});
