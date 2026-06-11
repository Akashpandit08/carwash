<?php

namespace App\Constants;

final class BookingStatus
{
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const PARTNER_ASSIGNED = 'partner_assigned';
    public const WORKER_ASSIGNED = 'worker_assigned';
    public const PICKUP_DRIVER_ASSIGNED = 'pickup_driver_assigned';
    public const DRIVER_ON_THE_WAY = 'driver_on_the_way';
    public const WORKER_ON_THE_WAY = 'worker_on_the_way';
    public const CAR_PICKED_UP = 'car_picked_up';
    public const REACHED_PARTNER = 'reached_partner';
    public const SERVICE_STARTED = 'service_started';
    public const SERVICE_COMPLETED = 'service_completed';
    public const OUT_FOR_DELIVERY = 'out_for_delivery';
    public const DELIVERED = 'delivered';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';

    public const ALL = [
        self::PENDING,
        self::CONFIRMED,
        self::PARTNER_ASSIGNED,
        self::WORKER_ASSIGNED,
        self::PICKUP_DRIVER_ASSIGNED,
        self::DRIVER_ON_THE_WAY,
        self::WORKER_ON_THE_WAY,
        self::CAR_PICKED_UP,
        self::REACHED_PARTNER,
        self::SERVICE_STARTED,
        self::SERVICE_COMPLETED,
        self::OUT_FOR_DELIVERY,
        self::DELIVERED,
        self::COMPLETED,
        self::CANCELLED,
    ];
}
