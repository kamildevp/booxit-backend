<?php

declare(strict_types=1);

namespace App\Enum\OrganizationMember;

use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\Trait\NormalizerGroupTrait;
use App\Enum\User\UserNormalizerGroup;

enum OrganizationMemberNormalizerGroup: string implements NormalizerGroupInterface
{
    use NormalizerGroupTrait;

    case PUBLIC = 'organization_member-public';
    case PRIVATE = 'organization_member-private';

    protected function appendGroups(): array
    {
        return UserNormalizerGroup::PUBLIC->normalizationGroups();
    }
}