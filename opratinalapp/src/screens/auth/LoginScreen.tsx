import React, { useState } from 'react';
import { View, Text, StyleSheet, Alert } from 'react-native';
import { AppInput } from '../../components/AppInput';
import { AppButton } from '../../components/AppButton';
import { sendOtp } from '../../api/authApi';

export const LoginScreen = ({ navigation }: any) => {
  const [phone, setPhone] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!phone) {
      Alert.alert('Error', 'Please enter your phone number');
      return;
    }
    setLoading(true);
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
      setLoading(false);
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
      
      <AppButton title="Send OTP" onPress={handleLogin} loading={loading} />
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
