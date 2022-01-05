<?php

namespace App\Enum;

class ResponseContentTypesEnum
{
    const JSON = "application/json";
    const XML = "application/xml";

    public static function getContentTypesList(): array
    {
        return [
            self::JSON => self::JSON,
            self::XML => self::XML
        ];
    }
}