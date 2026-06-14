import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Platform } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { DrawerActions } from '@react-navigation/native';

interface HeaderProps {
  title: string;
  showBack?: boolean;
  showMenu?: boolean;
  onLogout?: () => void;
}

export const Header: React.FC<HeaderProps> = ({ title, showBack = false, showMenu = false, onLogout }) => {
  const navigation = useNavigation<any>();
  const insets = useSafeAreaInsets();

  const handleLeftPress = () => {
    if (showBack && navigation.canGoBack()) {
      navigation.goBack();
    } else if (showMenu) {
      navigation.dispatch(DrawerActions.toggleDrawer());
    }
  };

  const navigateToNotifications = () => {
    navigation.navigate('NotificationsScreen');
  };

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <View style={styles.content}>
        {/* Left Side */}
        <TouchableOpacity style={styles.iconButton} onPress={handleLeftPress}>
          {showBack ? (
            <Ionicons name="arrow-back" size={24} color="#FFF" />
          ) : showMenu ? (
            <Ionicons name="menu" size={28} color="#FFF" />
          ) : (
            <View style={{ width: 24 }} />
          )}
        </TouchableOpacity>

        {/* Center Title */}
        <Text style={styles.title} numberOfLines={1}>{title}</Text>

        {/* Right Side */}
        <View style={styles.rightContainer}>
          <TouchableOpacity style={styles.iconButton} onPress={navigateToNotifications}>
            <Ionicons name="notifications-outline" size={24} color="#FFF" />
            {/* Unread badge can be added here if API supports it later */}
          </TouchableOpacity>
          {onLogout && (
            <TouchableOpacity style={[styles.iconButton, { marginLeft: 8 }]} onPress={onLogout}>
              <Ionicons name="log-out-outline" size={24} color="#FFF" />
            </TouchableOpacity>
          )}
        </View>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#0A2540',
    borderBottomWidth: 1,
    borderBottomColor: '#0A2540',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 3,
    elevation: 3,
  },
  content: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    height: 56,
    paddingHorizontal: 12,
  },
  title: {
    flex: 1,
    color: '#FFF',
    fontSize: 18,
    fontWeight: '600',
    textAlign: 'left',
    marginLeft: 16,
  },
  iconButton: {
    padding: 8,
    justifyContent: 'center',
    alignItems: 'center',
  },
  rightContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
});
