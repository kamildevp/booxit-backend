<?php

declare(strict_types=1);

namespace App\Enum\Organization;

use App\Enum\Trait\ValuesTrait;

enum OrganizationRole: string
{
    use ValuesTrait;

    case ADMIN = 'ADMIN';
    case MEMBER = 'MEMBER';
}