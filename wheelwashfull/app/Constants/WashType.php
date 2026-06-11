<?php

namespace App\Constants;

final class WashType
{
    public const DOOR_TO_DOOR = 'door_to_door';
    public const PICKUP_WASH = 'pickup_wash';

    public const ALL = [
        self::DOOR_TO_DOOR,
        self::PICKUP_WASH,
    ];
}
