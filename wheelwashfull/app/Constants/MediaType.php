<?php

namespace App\Constants;

final class MediaType
{
    public const BEFORE_IMAGE = 'before_image';
    public const AFTER_IMAGE = 'after_image';
    public const PICKUP_PROOF = 'pickup_proof';
    public const DELIVERY_PROOF = 'delivery_proof';
    public const PARTNER_SERVICE_PROOF = 'partner_service_proof';
    public const HANDOVER_PROOF = 'handover_proof';

    public const ALL = [
        self::BEFORE_IMAGE,
        self::AFTER_IMAGE,
        self::PICKUP_PROOF,
        self::DELIVERY_PROOF,
        self::PARTNER_SERVICE_PROOF,
        self::HANDOVER_PROOF,
    ];
}
