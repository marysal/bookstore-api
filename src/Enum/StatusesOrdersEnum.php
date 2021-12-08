<?php

namespace App\Enum;

class StatusesOrdersEnum
{
    const STATUS_PENDING = "pending";
    const STATUS_PROCESSED = "processed";
    const STATUS_DELIVERED = "delivered";

    public static function getTypesBooksList(): array
    {
        return [
            self::STATUS_PENDING => self::STATUS_PENDING,
            self::STATUS_PROCESSED => self::STATUS_PROCESSED,
            self::STATUS_DELIVERED => self::STATUS_DELIVERED,
        ];
    }
}