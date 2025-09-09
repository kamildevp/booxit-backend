<?php

declare(strict_types=1);

namespace App\DTO\OrganizationMember\Trait;

use App\Enum\Organization\OrganizationRole;
use Symfony\Component\Validator\Constraints as Assert;

trait OrganizationMemberRoleFieldDTO
{
    #[Assert\Choice(callback: 'getAllowedRoles', message: 'Parameter must be one of valid roles: {{ choices }}')]
    public readonly string $role; 

    public static function getAllowedRoles(): array
    {
        return OrganizationRole::values();
    }
}