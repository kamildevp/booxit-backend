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
    case BASE_INFO = 'organization_member-base_info';
    case USER = 'organization_member-user';
    case ORGANIZATION = 'organization_member-organization';
    case USER_MEMBERSHIPS = 'organization_member-user_organizations';

    protected function appendGroups(): array
    {
        return match($this){
            self::PUBLIC => [self::BASE_INFO->value, ...self::USER->normalizationGroups()],
            self::PRIVATE => self::PUBLIC->normalizationGroups(),
            self::USER => [self::BASE_INFO->value, UserNormalizerGroup::BASE_INFO->value],
            self::USER_MEMBERSHIPS => [self::BASE_INFO->value, self::ORGANIZATION->value, OrganizationNormalizerGroup::BASE_INFO->value],
            default => []
        };
    }
}