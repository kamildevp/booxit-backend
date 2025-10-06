<?php

declare(strict_types=1);

namespace App\Enum\Organization;

use App\Entity\Organization;
use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\NormalizerGroup;
use App\Enum\Trait\NormalizerGroupTrait;

enum OrganizationNormalizerGroup: string implements NormalizerGroupInterface
{
    use NormalizerGroupTrait;

    case PUBLIC = 'organization-public';
    case PRIVATE = 'organization-private';

    case BASE_INFO = 'organization-base_info';
    case DETAILS = 'organization-details';
    case SENSITIVE = 'organization-sensitive';
    case TIMESTAMP = Organization::class.NormalizerGroup::TIMESTAMP->value;
    case AUTHOR_INFO = Organization::class.NormalizerGroup::AUTHOR_INFO->value;

    protected function appendGroups(): array
    {
        return match($this){
            self::PUBLIC => [self::BASE_INFO->value, self::DETAILS->value, self::TIMESTAMP->value],
            self::PRIVATE => [self::SENSITIVE->value, ...self::PUBLIC->normalizationGroups()],
            self::AUTHOR_INFO => NormalizerGroup::AUTHOR_INFO->normalizationGroups(),
            default => []
        };
    }
}