<?php

declare(strict_types=1);

namespace App\DTO\OrganizationMember;

use App\DTO\ListFiltersDTO;
use App\DTO\User\UserBaseListFiltersDTO;
use App\Enum\Organization\OrganizationRole;
use Symfony\Component\Validator\Constraints as Assert;

class OrganizationMemberListFiltersDTO extends ListFiltersDTO 
{
    public function __construct(
        #[Assert\Valid]
        public readonly UserBaseListFiltersDTO $appUser = new UserBaseListFiltersDTO(),
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