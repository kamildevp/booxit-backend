<?php

declare(strict_types=1);

namespace App\Enum\OrganizationMember;

use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\Organization\OrganizationNormalizerGroup;
use App\Enum\Trait\NormalizerGroupTrait;
use App\Enum\User\UserNormalizerGroup;

enum OrganizationMemberNormalizerGroup: string implements NormalizerGroupInterface
{
    use NormalizerGroupTrait;

    case PUBLIC = 'organization_member-public';
    case PRIVATE = 'organization_member-private';
    case USER_MEMBERSHIPS = 'organization_member-user_organizations';

    protected function appendGroups(): array
    {
        return match($this){
            self::USER_MEMBERSHIPS => OrganizationNormalizerGroup::PUBLIC->normalizationGroups(),
            default => UserNormalizerGroup::PUBLIC->normalizationGroups()
        };
    }
}