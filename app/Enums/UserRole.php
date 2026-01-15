<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMINISTRATOR = 'administrator';

    case AGENT = 'agent';

    case SUPER_USER = 'super_user';

    case USER = 'user';

    case INTEGRATION = 'integration';

    case TECHNICIAN = 'technician';
}
