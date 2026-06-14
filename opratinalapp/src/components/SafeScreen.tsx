import React from 'react';
import { View, StyleSheet, KeyboardAvoidingView, Platform, ScrollView } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

interface SafeScreenProps {
  children: React.ReactNode;
  scrollable?: boolean;
  style?: any;
}

export const SafeScreen: React.FC<SafeScreenProps> = ({ children, scrollable = false, style }) => {
  const insets = useSafeAreaInsets();

  const content = scrollable ? (
    <ScrollView contentContainerStyle={[{ paddingBottom: Math.max(insets.bottom + 20, 20) }, style]}>
      {children}
    </ScrollView>
  ) : (
    <View style={[{ flex: 1, paddingBottom: Math.max(insets.bottom, 16) }, style]}>
      {children}
    </View>
  );

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      {content}
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F7FA',
  },
});
