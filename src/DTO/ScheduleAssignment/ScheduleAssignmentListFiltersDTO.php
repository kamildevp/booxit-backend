<?php

declare(strict_types=1);

namespace App\DTO\ScheduleAssignment;

use App\DTO\ListFiltersDTO;
use App\DTO\OrganizationMember\OrganizationMemberListFiltersDTO;
use App\Enum\Schedule\ScheduleAccessType;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\Compound as Compound;
use OpenApi\Attributes as OA;

class ScheduleAssignmentListFiltersDTO extends ListFiltersDTO 
{
    public function __construct(
        #[Assert\Valid]
        public readonly OrganizationMemberListFiltersDTO $organizationMember = new OrganizationMemberListFiltersDTO(),
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
        #[Compound\EnumSetRequirements(ScheduleAccessType::class)] 
        public readonly ?array $accessType  = null,
    )
    {

    }
}