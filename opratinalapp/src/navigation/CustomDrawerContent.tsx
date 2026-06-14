import React from 'react';
import { View, StyleSheet, Alert } from 'react-native';
import { DrawerContentScrollView, DrawerItemList, DrawerItem } from '@react-navigation/drawer';
import { Ionicons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useNavigation, CommonActions } from '@react-navigation/native';

export const CustomDrawerContent = (props: any) => {
  const navigation = useNavigation<any>();

  const handleLogout = () => {
    Alert.alert(
      'Logout',
      'Are you sure you want to log out?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Logout',
          style: 'destructive',
          onPress: async () => {
            await AsyncStorage.clear();
            navigation.dispatch(
              CommonActions.reset({
                index: 0,
                routes: [{ name: 'RoleRedirectScreen' }],
              })
            );
          },
        },
      ],
      { cancelable: true }
    );
  };

  return (
    <View style={styles.container}>
      <DrawerContentScrollView {...props}>
        <View style={styles.header}>
          <Ionicons name="person-circle" size={60} color="#0A2540" />
        </View>
        <DrawerItemList {...props} />
      </DrawerContentScrollView>
      <View style={styles.footer}>
        <DrawerItem
          label="Logout"
          icon={({ color, size }) => <Ionicons name="log-out-outline" color="#D32F2F" size={size} />}
          labelStyle={{ color: '#D32F2F', fontWeight: 'bold' }}
          onPress={handleLogout}
        />
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  header: {
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#EEE',
    marginBottom: 10,
    alignItems: 'center',
  },
  footer: {
    borderTopWidth: 1,
    borderTopColor: '#EEE',
    paddingVertical: 10,
    marginBottom: 20,
  },
});
