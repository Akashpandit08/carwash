import { apiClient, unwrap } from './client';

export async function checkoutPayment(paymentId: string | number) {
  const response = await apiClient.get(`/customer/payments/${paymentId}/checkout`);
  return unwrap(response.data);
}

export async function verifyPayment(paymentId: string | number, payload: Record<string, string>) {
  const response = await apiClient.post(`/customer/payments/${paymentId}/verify`, payload);
  return unwrap(response.data);
}

export async function markPaymentSuccess(paymentId: string | number, payload: Record<string, string>) {
  const response = await apiClient.post(`/customer/payments/${paymentId}/success`, payload);
  return unwrap(response.data);
}
