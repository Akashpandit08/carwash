import '@/global.css';
import { Platform } from 'react-native';

export const Brand = {
  // Primary palette
  royalBlue: '#1A56DB',
  royalBlueDark: '#1040B0',
  royalBlueDeep: '#0D2F8A',
  aqua: '#06B6D4',
  aquaLight: '#67E8F9',
  aquaMid: '#22D3EE',
  freshGreen: '#10B981',
  freshGreenLight: '#6EE7B7',

  // Gradients (arrays for LinearGradient)
  gradientHero: ['#1A56DB', '#06B6D4'] as string[],
  gradientCard: ['#1040B0', '#1A56DB'] as string[],
  gradientGreen: ['#059669', '#10B981'] as string[],
  gradientAqua: ['#0891B2', '#06B6D4'] as string[],

  // Neutrals
  white: '#FFFFFF',
  offWhite: '#F8FAFF',
  surface: '#EEF2FF',
  surfaceCard: '#FFFFFF',
  border: '#E2E8F0',
  borderLight: '#F1F5F9',

  // Text
  textPrimary: '#0F172A',
  textSecondary: '#64748B',
  textMuted: '#94A3B8',
  textOnDark: '#FFFFFF',
  textOnDarkSub: 'rgba(255,255,255,0.75)',

  // Status
  success: '#10B981',
  warning: '#F59E0B',
  error: '#EF4444',
  info: '#3B82F6',

  // Overlay
  overlay: 'rgba(15,23,42,0.45)',
  cardShadow: 'rgba(26,86,219,0.12)',
} as const;

export const Spacing = {
  half: 2,
  one: 4,
  two: 8,
  three: 12,
  four: 16,
  five: 20,
  xs: 4,
  sm: 8,
  md: 12,
  base: 16,
  lg: 20,
  xl: 24,
  xxl: 32,
  xxxl: 48,
} as const;

export const Colors = {
  light: {
    text: Brand.textPrimary,
    background: Brand.white,
    tint: Brand.royalBlue,
    icon: Brand.textSecondary,
    textSecondary: Brand.textSecondary,
    backgroundElement: Brand.offWhite,
    backgroundSelected: Brand.surface,
    tabIconDefault: Brand.textMuted,
    tabIconSelected: Brand.royalBlue,
  },
  dark: {
    text: Brand.white,
    background: Brand.textPrimary,
    tint: Brand.aquaLight,
    icon: Brand.textMuted,
    textSecondary: Brand.textMuted,
    backgroundElement: '#172033',
    backgroundSelected: '#1E2A44',
    tabIconDefault: Brand.textMuted,
    tabIconSelected: Brand.aquaLight,
  },
};

export type ThemeColor = keyof typeof Colors.light;

export const Fonts = {
  mono: Platform.select({ ios: 'Menlo', android: 'monospace', default: 'monospace' }),
} as const;

export const Radius = {
  sm: 8,
  md: 12,
  lg: 16,
  xl: 20,
  xxl: 24,
  round: 999,
} as const;

export const Typography = {
  hero: { fontSize: 28, fontWeight: '800' as const, letterSpacing: -0.5 },
  h1: { fontSize: 24, fontWeight: '700' as const, letterSpacing: -0.3 },
  h2: { fontSize: 20, fontWeight: '700' as const },
  h3: { fontSize: 17, fontWeight: '600' as const },
  body: { fontSize: 15, fontWeight: '400' as const },
  bodyMed: { fontSize: 15, fontWeight: '500' as const },
  small: { fontSize: 13, fontWeight: '400' as const },
  smallMed: { fontSize: 13, fontWeight: '500' as const },
  caption: { fontSize: 11, fontWeight: '500' as const, letterSpacing: 0.4 },
  price: { fontSize: 22, fontWeight: '800' as const },
} as const;

export const Shadow = {
  card: {
    shadowColor: Brand.royalBlue,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.10,
    shadowRadius: 12,
    elevation: 4,
  },
  strong: {
    shadowColor: Brand.royalBlueDeep,
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.18,
    shadowRadius: 20,
    elevation: 8,
  },
  subtle: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 6,
    elevation: 2,
  },
} as const;

export const BottomTabInset = Platform.select({ ios: 50, android: 70 }) ?? 0;
export const MaxContentWidth = 800;
