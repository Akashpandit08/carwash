<?php

namespace App\Constants;

final class UserRole
{
    public const ADMIN = 'admin';
    public const SUPER_ADMIN = 'super_admin';
    public const CITY_ADMIN = 'city_admin';
    public const CUSTOMER = 'customer';
    public const PARTNER = 'partner';
    public const WORKER = 'worker';
    public const PICKUP_DRIVER = 'pickup_driver';

    public const ALL = [
        self::ADMIN,
        self::SUPER_ADMIN,
        self::CITY_ADMIN,
        self::CUSTOMER,
        self::PARTNER,
        self::WORKER,
        self::PICKUP_DRIVER,
    ];

    public const ADMIN_ROLES = [
        self::ADMIN,
        self::SUPER_ADMIN,
        self::CITY_ADMIN,
    ];

    public static function isAdminRole(?string $role): bool
    {
        return in_array($role, self::ADMIN_ROLES, true);
    }

    public static function isSuperAdminRole(?string $role): bool
    {
        return in_array($role, [self::ADMIN, self::SUPER_ADMIN], true);
    }
}
