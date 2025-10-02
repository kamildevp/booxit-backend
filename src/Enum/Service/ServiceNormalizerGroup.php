<?php

declare(strict_types=1);

namespace App\Enum\Service;

use App\Entity\Service;
use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\NormalizerGroup;
use App\Enum\Organization\OrganizationNormalizerGroup;
use App\Enum\Trait\NormalizerGroupTrait;

enum ServiceNormalizerGroup: string implements NormalizerGroupInterface
{
    use NormalizerGroupTrait;

    case PUBLIC = 'service-public';
    case PRIVATE = 'service-private';
    case BASE_INFO = 'service-base_info';
    case ORGANIZATION = 'service-organization';
    case TIMESTAMP = Service::class.NormalizerGroup::TIMESTAMP->value;
    case AUTHOR_INFO = Service::class.NormalizerGroup::AUTHOR_INFO->value;

    protected function appendGroups(): array
    {
        return match($this){
            self::PUBLIC => [
                self::BASE_INFO->value, 
                self::TIMESTAMP->value, 
                ...self::ORGANIZATION->normalizationGroups(),
            ],
            self::PRIVATE => self::PUBLIC->normalizationGroups(),
            self::ORGANIZATION => OrganizationNormalizerGroup::BASE_INFO->normalizationGroups(),
            self::AUTHOR_INFO => NormalizerGroup::AUTHOR_INFO->normalizationGroups(),
            default => []
        };
    }
}