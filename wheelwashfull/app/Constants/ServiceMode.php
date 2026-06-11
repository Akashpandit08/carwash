<?php

namespace App\Constants;

final class ServiceMode
{
    public const DOORSTEP = 'doorstep';
    public const PARTNER_CENTER = 'partner_center';
    public const PICKUP_DROP = 'pickup_drop';

    public const ALL = [
        self::DOORSTEP,
        self::PARTNER_CENTER,
        self::PICKUP_DROP,
    ];
}
