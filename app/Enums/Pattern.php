<?php

namespace App\Enums;

enum Pattern: string
{
    case SQUARE = 'square';

    case RECTANGULAR = 'rectangular';

    case TRIANGULAR = 'triangular';

    case QUINCUNX = 'quincunx';

    case CONCENTRIC_CIRCLES = 'concentric_circles';

    case OTHER = 'other';
}
