import { apiClient, unwrap } from './client';

export type CustomerUser = {
  id: number | string;
  name?: string;
  firstName?: string;
  mobile_number?: string;
  phone?: string;
  email?: string;
  role?: string;
};

export async function sendOtp(mobileNumber: string) {
  const response = await apiClient.post('/auth/send-otp', { mobile_number: mobileNumber });
  return unwrap<{ mobile_number: string; otp?: string; expires_in?: string }>(response.data);
}

export async function verifyOtp(mobileNumber: string, otp: string) {
  const response = await apiClient.post('/auth/verify-otp', {
    mobile_number: mobileNumber,
    otp,
  });
  return unwrap<{ token: string; user: CustomerUser; token_type?: string }>(response.data);
}

export async function getMe() {
  const response = await apiClient.get('/auth/me');
  const data = unwrap<{ user: CustomerUser }>(response.data);
  return data.user;
}

export async function logout() {
  await apiClient.post('/auth/logout');
}
