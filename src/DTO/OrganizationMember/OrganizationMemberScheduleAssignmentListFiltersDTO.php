<?php

declare(strict_types=1);

namespace App\DTO\OrganizationMember;

use App\DTO\ListFiltersDTO;
use App\DTO\Schedule\ScheduleBaseListFiltersDTO;
use App\Enum\Schedule\ScheduleAccessType;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\Compound as Compound;
use OpenApi\Attributes as OA;

class OrganizationMemberScheduleAssignmentListFiltersDTO extends ListFiltersDTO 
{
    public function __construct(
        #[Assert\Valid]
        public readonly ScheduleBaseListFiltersDTO $schedule = new ScheduleBaseListFiltersDTO(),
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
        #[Compound\EnumSetRequirements(ScheduleAccessType::class)] 
        public readonly ?array $accessType  = null,
    )
    {

    }
}