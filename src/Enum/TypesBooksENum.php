<?php

namespace App\Enum;

class TypesBooksENum
{
    const TYPE_POETRY = "poetry";
    const TYPE_PROSE = "prose";

    public static function getTypesBooksList(): array
    {
        return [
            self::TYPE_POETRY => self::TYPE_POETRY,
            self::TYPE_PROSE => self::TYPE_PROSE
        ];
    }
}