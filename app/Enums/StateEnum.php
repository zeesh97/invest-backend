<?php

namespace App\Enums;

class StateEnum
{
    public const NEW = 1;
    public const RENEW = 2;
    public const REPAIR = 3;
    public const REPLACE = 4;
    public const TEMPORARY = 5;
    public const UPGRADE = 6;

    public static function getDescription(int $value): string
    {
        return self::$values[$value] ?? 'Unknown';
    }

    public static function toArray(): array
    {
        return [
            self::NEW => 'NEW',
            self::RENEW => 'RENEW',
            self::REPAIR => 'REPAIR',
            self::REPLACE => 'REPLACE',
            self::TEMPORARY => 'TEMPORARY',
            self::UPGRADE => 'UPGRADE',
        ];
    }
}
