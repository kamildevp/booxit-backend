<?php

declare(strict_types=1);

namespace App\Enum\Organization;

use App\Enum\Trait\ValuesTrait;

enum OrganizationTier: string
{
    use ValuesTrait;

    case BASIC = 'BASIC';
    case PREMIUM = 'PREMIUM';
}