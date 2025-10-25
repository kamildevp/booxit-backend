<?php

declare(strict_types=1);

namespace App\DTO\OrganizationMember;

use App\DTO\ListFiltersDTO;
use App\DTO\User\UserBaseListFiltersDTO;
use App\Enum\Organization\OrganizationRole;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\Compound as Compound;
use OpenApi\Attributes as OA;

class OrganizationMemberListFiltersDTO extends ListFiltersDTO 
{
    public function __construct(
        #[Assert\Valid]
        public readonly UserBaseListFiltersDTO $appUser = new UserBaseListFiltersDTO(),
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
        #[Compound\EnumSetRequirements(OrganizationRole::class)] 
        public readonly ?array $role = null,
    )
    {

    }
}