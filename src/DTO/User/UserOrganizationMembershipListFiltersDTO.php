<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\ListFiltersDTO;
use App\DTO\Organization\OrganizationBaseListFiltersDTO;
use App\Enum\Organization\OrganizationRole;
use Symfony\Component\Validator\Constraints as Assert;

class UserOrganizationMembershipListFiltersDTO extends ListFiltersDTO 
{
    public function __construct(
        #[Assert\Valid]
        public readonly OrganizationBaseListFiltersDTO $organization = new OrganizationBaseListFiltersDTO,
        #[Assert\Choice(callback: 'getAllowedRoles', message: 'Parameter must be one of valid roles: {{ choices }}')]
        public readonly ?string $role = null,
    )
    {

    }

    public static function getAllowedRoles(): array
    {
        return OrganizationRole::values();
    }
}