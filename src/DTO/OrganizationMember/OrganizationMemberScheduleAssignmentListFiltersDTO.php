<?php

declare(strict_types=1);

namespace App\DTO\OrganizationMember;

use App\DTO\ListFiltersDTO;
use App\DTO\Schedule\ScheduleBaseListFiltersDTO;
use App\Enum\Schedule\ScheduleAccessType;
use Symfony\Component\Validator\Constraints as Assert;

class OrganizationMemberScheduleAssignmentListFiltersDTO extends ListFiltersDTO 
{
    public function __construct(
        #[Assert\Valid]
        public readonly ScheduleBaseListFiltersDTO $schedule = new ScheduleBaseListFiltersDTO(),
        #[Assert\Choice(callback: [ScheduleAccessType::class, 'values'], message: 'Parameter must be one of valid access types: {{ choices }}')]
        public readonly ?string $accessType = null,
    )
    {

    }
}