<?php

namespace App\Enums;

enum Plans: string
{
    case DAY = 'day';
    case MONTH = 'month';
    case YEAR = 'year';
    case WEEK = 'week';
    case CUSTOM = 'custom';

    case ONE_TIME = 'one_time';
    case RECURRING = 'recurring';
    
    public static function labels(): array
    {
        return [
            self::DAY->value => 'Daily',
            self::WEEK->value => 'Weekly',
            self::MONTH->value => 'Monthly',
            self::YEAR->value => 'Yearly',
            self::CUSTOM->value => 'Custom'
        ];
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}