<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\ListFiltersDTO;
use App\DTO\Organization\OrganizationBaseListFiltersDTO;
use App\Enum\Organization\OrganizationRole;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\Compound as Compound;
use OpenApi\Attributes as OA;

class UserOrganizationMembershipListFiltersDTO extends ListFiltersDTO 
{
    public function __construct(
        #[Assert\Valid]
        public readonly OrganizationBaseListFiltersDTO $organization = new OrganizationBaseListFiltersDTO,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
        #[Compound\EnumSetRequirements(OrganizationRole::class)] 
        public readonly ?array $role = null,
    )
    {

    }
}