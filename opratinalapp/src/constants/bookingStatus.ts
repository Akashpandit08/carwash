export const BookingStatus = {
  PENDING: 'pending',
  CONFIRMED: 'confirmed',
  ASSIGNED_PICKUP_DRIVER: 'assigned_pickup_driver',
  DRIVER_STARTED: 'driver_started',
  ARRIVED_AT_CUSTOMER: 'arrived_at_customer',
  VEHICLE_PICKED_UP: 'vehicle_picked_up',
  VEHICLE_DROPPED_AT_PARTNER: 'vehicle_dropped_at_partner',
  ASSIGNED_WORKER: 'assigned_worker',
  WASH_STARTED: 'wash_started',
  WASH_COMPLETED: 'wash_completed',
  READY_FOR_DELIVERY: 'ready_for_delivery',
  OUT_FOR_DELIVERY: 'out_for_delivery',
  DELIVERED: 'delivered',
  CANCELLED: 'cancelled',
} as const;

export type BookingStatusType = typeof BookingStatus[keyof typeof BookingStatus];
