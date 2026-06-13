import apiClient from './client';

export const sendOtp = async (phone: string) => {
  const response = await apiClient.post('/auth/send-otp', { mobile_number: phone });
  const data = response.data;
  const devOtp = data?.otp || data?.data?.otp || data?.dev_otp || data?.data?.dev_otp;
  return { ...data, otp: devOtp };
};

export const verifyOtp = (phone: string, otp: string) => apiClient.post('/auth/verify-otp', { mobile_number: phone, otp });
export const loginWithPassword = (phone: string, password: string) => apiClient.post('/auth/login', { mobile_number: phone, password });

export const logout = () => apiClient.post('/auth/logout');
