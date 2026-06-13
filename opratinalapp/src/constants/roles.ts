export const UserRole = {
  ADMIN: 'admin',
  SUPER_ADMIN: 'super_admin',
  CITY_ADMIN: 'city_admin',
  CUSTOMER: 'customer',
  PARTNER: 'partner',
  WORKER: 'worker',
  PICKUP_DRIVER: 'pickup_driver',
} as const;

export const ADMIN_ROLES = [UserRole.ADMIN, UserRole.SUPER_ADMIN, UserRole.CITY_ADMIN] as const;

export type UserRoleType = typeof UserRole[keyof typeof UserRole];
