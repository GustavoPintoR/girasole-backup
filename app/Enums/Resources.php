<?php

namespace App\Enums;

enum Resources: string
{
    case PROVINCES = 'provinces';
    case CITIES = 'cities';
    case REGIONS = 'regions';
    case USERS = 'users';
    case POSTAL_CODES = 'postal_codes';

    public function getName(): string
    {
        return match ($this) {
            self::PROVINCES => 'province',
            self::CITIES => 'city',
            self::REGIONS => 'region',
            self::POSTAL_CODES => 'postal_code',
        };
    }

    public function getPluralName(): string
    {
        return match ($this) {
            self::PROVINCES => 'provinces',
            self::CITIES => 'cities',
            self::REGIONS => 'regions',
            self::POSTAL_CODES => 'postal_codes',
        };
    }
}
