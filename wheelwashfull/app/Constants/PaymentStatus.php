<?php

namespace App\Constants;

final class PaymentStatus
{
    public const PENDING = 'pending';
    public const PAID = 'paid';
    public const FAILED = 'failed';
    public const REFUNDED = 'refunded';

    public const ALL = [
        self::PENDING,
        self::PAID,
        self::FAILED,
        self::REFUNDED,
    ];
}
