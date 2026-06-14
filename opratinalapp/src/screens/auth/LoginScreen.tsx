import React, { useState } from 'react';
import { View, Text, StyleSheet, Alert } from 'react-native';
import { AppInput } from '../../components/AppInput';
import { AppButton } from '../../components/AppButton';
import { loginWithPassword, sendOtp } from '../../api/authApi';
import AsyncStorage from '@react-native-async-storage/async-storage';

export const LoginScreen = ({ navigation }: any) => {
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [otpLoading, setOtpLoading] = useState(false);
  const [passwordLoading, setPasswordLoading] = useState(false);

  const busy = otpLoading || passwordLoading;

  const handleLogin = async () => {
    if (busy) return;
    if (!phone) {
      Alert.alert('Error', 'Please enter your phone number');
      return;
    }
    setOtpLoading(true);
    try {
      const response = await sendOtp(phone);
      navigation.navigate('OtpScreen', { 
        phone, 
        mobile_number: phone, 
        otp: response.otp 
      });
    } catch (e: any) {
      Alert.alert('Error', e.response?.data?.message || 'Failed to send OTP');
      // If backend is not perfect yet, allow bypass for testing purposes
      navigation.navigate('OtpScreen', { phone });
    } finally {
      setOtpLoading(false);
    }
  };

  const handlePasswordLogin = async () => {
    if (busy) return;
    if (!phone || !password) {
      Alert.alert('Error', 'Please enter your phone number and password');
      return;
    }

    setPasswordLoading(true);
    try {
      const response = await loginWithPassword(phone, password);
      const data = response.data;
      const token = data.token || data.access_token || data.data?.token;
      const user = data.user || data.data?.user;

      if (!token || !user) {
        throw new Error('Invalid response format');
      }

      await AsyncStorage.setItem('userToken', token);
      await AsyncStorage.setItem('userData', JSON.stringify(user));
      navigation.replace('RoleRedirectScreen');
    } catch (e: any) {
      Alert.alert('Error', e.response?.data?.message || 'Invalid credentials');
    } finally {
      setPasswordLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>WashMate Operations</Text>
      <Text style={styles.subtitle}>Login to your account</Text>
      
      <AppInput 
        label="Phone Number" 
        placeholder="Enter your mobile number" 
        keyboardType="phone-pad"
        value={phone}
        onChangeText={setPhone}
      />

      <AppInput
        label="Password"
        placeholder="Enter password"
        secureTextEntry
        value={password}
        onChangeText={setPassword}
      />
      
      <AppButton
        title="Login with Password"
        onPress={handlePasswordLogin}
        loading={passwordLoading}
        disabled={otpLoading}
      />
      <AppButton
        title="Send OTP"
        onPress={handleLogin}
        loading={otpLoading}
        disabled={passwordLoading}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 24,
    justifyContent: 'center',
    backgroundColor: '#FFF',
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#007BFF',
    marginBottom: 8,
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 16,
    color: '#666',
    marginBottom: 32,
    textAlign: 'center',
  },
});
