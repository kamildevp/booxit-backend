<?php

declare(strict_types=1);

namespace App\Enum\Schedule;

use App\Entity\Schedule;
use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\NormalizerGroup;
use App\Enum\Organization\OrganizationNormalizerGroup;
use App\Enum\Trait\NormalizerGroupTrait;

enum ScheduleNormalizerGroup: string implements NormalizerGroupInterface
{
    use NormalizerGroupTrait;

    case PUBLIC = 'schedule-public';
    case PRIVATE = 'schedule-private';
    case BASE_INFO = 'schedule-base_info';
    case ORGANIZATION = 'schedule-organization';
    case TIMESTAMP = Schedule::class.NormalizerGroup::TIMESTAMP->value;
    case AUTHOR_INFO = Schedule::class.NormalizerGroup::AUTHOR_INFO->value;

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