<?php

declare(strict_types=1);

namespace App\DTO\ScheduleAssignment;

use App\DTO\ListFiltersDTO;
use App\DTO\OrganizationMember\OrganizationMemberListFiltersDTO;
use App\Enum\Schedule\ScheduleAccessType;
use Symfony\Component\Validator\Constraints as Assert;

class ScheduleAssignmentListFiltersDTO extends ListFiltersDTO 
{
    public function __construct(
        #[Assert\Valid]
        public readonly OrganizationMemberListFiltersDTO $organizationMember = new OrganizationMemberListFiltersDTO(),
        #[Assert\Choice(callback: [ScheduleAccessType::class, 'values'], message: 'Parameter must be one of valid access types: {{ choices }}')]
        public readonly ?string $accessType = null,
    )
    {

    }
}