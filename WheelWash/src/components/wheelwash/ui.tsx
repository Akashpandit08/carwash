import { Ionicons } from '@expo/vector-icons';
import { Image, ImageSourcePropType, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { BORDER, MUTED, PRIMARY, SUCCESS, TEXT } from '@/lib/wheelwash-data';

export function Logo({ size = 'md' }: { size?: 'sm' | 'md' | 'lg' }) {
  const markSize = size === 'lg' ? 58 : size === 'sm' ? 34 : 44;
  const fontSize = size === 'lg' ? 34 : size === 'sm' ? 25 : 30;
  return (
    <View style={styles.logoRow}>
      <View style={[styles.logoMark, { width: markSize, height: markSize, borderRadius: markSize / 2 }]}>
        <Ionicons name="car-sport" size={markSize * 0.48} color="#fff" />
      </View>
      <Text style={[styles.logoText, { fontSize }]}>WheelWash</Text>
    </View>
  );
}

export function ScreenHeader({
  title,
  showBack,
  onBack,
}: {
  title?: string;
  showBack?: boolean;
  onBack?: () => void;
}) {
  return (
    <View style={styles.header}>
      <TouchableOpacity style={styles.headerIcon} onPress={onBack} activeOpacity={0.75}>
        <Ionicons name={showBack ? 'arrow-back' : 'menu'} size={26} color={TEXT} />
      </TouchableOpacity>
      {title ? <Text style={styles.headerTitle}>{title}</Text> : <Logo />}
      <TouchableOpacity style={styles.headerIcon} activeOpacity={0.75}>
        <Ionicons name="notifications-outline" size={25} color={TEXT} />
        <View style={styles.notifyDot} />
      </TouchableOpacity>
    </View>
  );
}

export function Card({ children, style }: { children: React.ReactNode; style?: object }) {
  return <View style={[styles.card, style]}>{children}</View>;
}

export function PrimaryButton({
  title,
  icon,
  onPress,
  outline,
  disabled,
  style,
}: {
  title: string;
  icon?: keyof typeof Ionicons.glyphMap;
  onPress?: () => void;
  outline?: boolean;
  disabled?: boolean;
  style?: any;
}) {
  return (
    <TouchableOpacity style={[styles.button, outline && styles.buttonOutline, disabled && styles.buttonDisabled, style]} onPress={onPress} activeOpacity={0.86} disabled={disabled}>
      {icon && <Ionicons name={icon} size={22} color={outline ? PRIMARY : '#fff'} />}
      <Text style={[styles.buttonText, outline && styles.buttonOutlineText, disabled && styles.buttonDisabledText]}>{title}</Text>
    </TouchableOpacity>
  );
}

export function SelectedBadge() {
  return (
    <View style={styles.selectedBadge}>
      <Text style={styles.selectedText}>Selected</Text>
      <Ionicons name="checkmark-circle" size={18} color={SUCCESS} />
    </View>
  );
}

export function CarThumb({ uri, source, size = 92 }: { uri?: string; source?: ImageSourcePropType; size?: number }) {
  return (
    <Image
      source={source ?? { uri }}
      style={[styles.carThumb, { width: size, height: size * 0.72 }]}
      resizeMode="cover"
    />
  );
}

const styles = StyleSheet.create({
  logoRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8 },
  logoMark: {
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: PRIMARY,
  },
  logoText: { color: '#1062D9', fontWeight: '900', letterSpacing: 0 },
  header: {
    minHeight: 64,
    paddingHorizontal: 24,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  headerIcon: { padding: 8, alignItems: 'center', justifyContent: 'center' },
  headerTitle: { flex: 1, textAlign: 'center', color: TEXT, fontSize: 27, fontWeight: '800' },
  notifyDot: { position: 'absolute', top: 8, right: 8, width: 10, height: 10, borderRadius: 5, backgroundColor: '#FF3B30', borderWidth: 1.5, borderColor: '#fff' },
  card: {
    backgroundColor: '#fff',
    borderRadius: 18,
    borderWidth: 1,
    borderColor: BORDER,
    shadowColor: '#0B4DA2',
    shadowOpacity: 0.09,
    shadowRadius: 14,
    shadowOffset: { width: 0, height: 6 },
    elevation: 4,
  },
  button: {
    minHeight: 62,
    borderRadius: 16,
    backgroundColor: PRIMARY,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
    gap: 10,
    paddingHorizontal: 24,
  },
  buttonDisabled: { backgroundColor: '#D8E1EC' },
  buttonOutline: { backgroundColor: '#fff', borderWidth: 2, borderColor: PRIMARY },
  buttonText: { color: '#fff', fontSize: 20, fontWeight: '800' },
  buttonDisabledText: { color: '#778397' },
  buttonOutlineText: { color: PRIMARY },
  selectedBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: '#DDF8EA',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 10,
  },
  selectedText: { color: '#0A9B59', fontSize: 14, fontWeight: '800' },
  carThumb: { borderRadius: 12, backgroundColor: '#E8F3FF' },
});
