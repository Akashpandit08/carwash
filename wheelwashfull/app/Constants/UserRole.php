<?php

namespace App\Constants;

final class UserRole
{
    public const ADMIN = 'admin';
    public const CUSTOMER = 'customer';
    public const PARTNER = 'partner';
    public const WORKER = 'worker';
    public const PICKUP_DRIVER = 'pickup_driver';

    public const ALL = [
        self::ADMIN,
        self::CUSTOMER,
        self::PARTNER,
        self::WORKER,
        self::PICKUP_DRIVER,
    ];
}
