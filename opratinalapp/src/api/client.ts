import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Alert } from 'react-native';
import { API_BASE_URL } from '../config/api';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 15000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

apiClient.interceptors.request.use(
  async (config) => {
    try {
      const token = await AsyncStorage.getItem('userToken');
      if (token && config.headers) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } catch (e) {
      console.error('Error attaching token', e);
    }
    return config;
  },
  (error) => Promise.reject(error)
);

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    const config = error.config;
    const url = config?.url;
    const method = config?.method?.toUpperCase();
    const requestBody = config?.data;
    const status = error.response?.status;
    const responseData = error.response?.data;
    const message = responseData?.message || error.message;

    if (__DEV__) {
      console.error(`[API ERROR] ${method} ${url}`);
      console.error(`[API STATUS] ${status}`);
      console.error(`[API REQUEST]`, requestBody);
      console.error(`[API RESPONSE]`, responseData);
    }

    if (status === 401) {
      Alert.alert('Session expired', 'Please log in again.');
    }

    return Promise.reject(error);
  }
);

export { API_BASE_URL };
export default apiClient;
