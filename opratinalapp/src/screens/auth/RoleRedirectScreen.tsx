import React, { useEffect } from 'react';
import { View, ActivityIndicator, Alert, StyleSheet } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { UserRole } from '../../constants/roles';
import { registerForPushNotifications } from '../../services/notificationService';

export const RoleRedirectScreen = ({ navigation }: any) => {
  useEffect(() => {
    const redirectUser = async () => {
      try {
        const userDataStr = await AsyncStorage.getItem('userData');
        if (!userDataStr) {
          navigation.replace('Auth');
          return;
        }

        const user = JSON.parse(userDataStr);

        // Register for push notifications after identifying the user
        if (user.id && user.role) {
          registerForPushNotifications(user.id, user.role).catch((err: any) =>
            console.error('Push notification registration failed:', err)
          );
        }
        
        switch (user.role) {
          case UserRole.ADMIN:
            navigation.replace('Admin');
            break;
          case UserRole.PARTNER:
            navigation.replace('Partner');
            break;
          case UserRole.WORKER:
            navigation.replace('Worker');
            break;
          case UserRole.PICKUP_DRIVER:
            navigation.replace('PickupDriver');
            break;
          case UserRole.CUSTOMER:
            Alert.alert('Wrong App', 'Please use the customer app.', [
              { text: 'OK', onPress: () => navigation.replace('Auth') }
            ]);
            break;
          default:
            Alert.alert('Error', 'Unknown role');
            navigation.replace('Auth');
        }
      } catch (e) {
        navigation.replace('Auth');
      }
    };

    redirectUser();
  }, [navigation]);

  return (
    <View style={styles.container}>
      <ActivityIndicator size="large" color="#007BFF" />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#FFF',
  },
});
