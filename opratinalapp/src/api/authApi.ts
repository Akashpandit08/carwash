import apiClient from './client';

export const sendOtp = (phone: string) => apiClient.post('/auth/send-otp', { phone });

export const verifyOtp = (phone: string, otp: string) => apiClient.post('/auth/verify-otp', { phone, otp });

export const logout = () => apiClient.post('/auth/logout');
