import { apiClient, unwrap } from './client';

export async function createReview(bookingId: string | number, rating: number, comment: string) {
  const response = await apiClient.post(`/customer/bookings/${bookingId}/review`, {
    rating,
    comment,
  });
  return unwrap(response.data);
}
