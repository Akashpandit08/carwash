export type UserLocation = {
  id?: string | number;
  latitude?: number;
  longitude?: number;
  city: string;
  region: string;
  pincode: string;
  area?: string;
  fullAddress: string;
};

export type Vehicle = {
  id: string;
  type: string;
  brand: string;
  model: string;
  registrationNumber: string;
  color: string;
};

export const PRIMARY = '#0877F2';
export const TEXT = '#101522';
export const MUTED = '#667085';
export const BORDER = '#E3EAF3';
export const SURFACE = '#F7FBFF';
export const SUCCESS = '#14B86E';

export const STORAGE_KEYS = {
  user: 'ww_user',
  customerToken: 'customer_token',
  location: 'user_location',
  selectedVehicle: 'selected_vehicle',
  selectedService: 'selected_service',
  vehicles: 'ww_vehicles',
  bookingId: 'booking_id',
  vehicleId: 'vehicle_id',
  serviceId: 'service_id',
  paymentId: 'payment_id',
  reviewId: 'review_id',
  addressId: 'address_id',
  couponId: 'coupon_id',
};

export const defaultVehicle: Vehicle = {
  id: 'vehicle-demo',
  type: 'Hatchback',
  brand: 'Hyundai',
  model: 'i20',
  registrationNumber: 'UP80AB1234',
  color: 'White',
};

export const defaultLocation: UserLocation = {
  city: 'Agra',
  region: 'UP',
  pincode: '282005',
  area: 'Dayal Bagh',
  fullAddress: 'Home, Dayal Bagh, Agra, Uttar Pradesh - 282005',
};

export const serviceCategories = [
  { title: 'Exterior Wash', icon: 'car-sport-outline', tone: '#E8F2FF', color: PRIMARY },
  { title: 'Interior Cleaning', icon: 'sparkles-outline', tone: '#E6FAF7', color: '#0EA5A3' },
  { title: 'Foam Wash', icon: 'water-outline', tone: '#EAF7FF', color: '#0B86D8' },
  { title: 'Full Detailing', icon: 'shield-checkmark-outline', tone: '#F0E9FF', color: '#7048E8' },
];
