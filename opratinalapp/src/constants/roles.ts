export const UserRole = {
  ADMIN: 'admin',
  CUSTOMER: 'customer',
  PARTNER: 'partner',
  WORKER: 'worker',
  PICKUP_DRIVER: 'pickup_driver',
} as const;

export type UserRoleType = typeof UserRole[keyof typeof UserRole];
