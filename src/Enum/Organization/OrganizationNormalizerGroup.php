<?php

declare(strict_types=1);

namespace App\Enum\Organization;

use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\NormalizerGroup;
use App\Enum\Trait\NormalizerGroupTrait;

enum OrganizationNormalizerGroup: string implements NormalizerGroupInterface
{
    use NormalizerGroupTrait;

    case PUBLIC = 'organization-public';
    case PRIVATE = 'organization-private';

    protected function appendGroups(): array
    {
        $caseGroups = match($this){
            OrganizationNormalizerGroup::PUBLIC => [],
            OrganizationNormalizerGroup::PRIVATE => [NormalizerGroup::AUTHOR_INFO->value],
        };

        return array_merge($caseGroups, [
            NormalizerGroup::TIMESTAMP->value,
        ]);
    }
}