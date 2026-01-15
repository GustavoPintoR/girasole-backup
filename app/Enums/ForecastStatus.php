<?php

namespace App\Enums;

enum ForecastStatus: string
{
    case FAILED = 'failed';

    case SUCCESS = 'success';

    case PENDING = 'pending';
}
