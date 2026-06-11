<?php

namespace App\Constants;

final class PayoutStatus
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const PAID = 'paid';
    public const FAILED = 'failed';

    public const ALL = [
        self::PENDING,
        self::APPROVED,
        self::PAID,
        self::FAILED,
    ];
}
