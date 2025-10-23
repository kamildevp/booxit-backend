<?php

declare(strict_types=1);

namespace App\DTO\ScheduleService;

use App\DTO\AbstractDTO;
use App\Entity\Service;
use App\Validator\Constraints as CustomAssert;

class ScheduleServiceAddDTO extends AbstractDTO 
{
    public function __construct(
        #[CustomAssert\EntityExists(Service::class, commonRelations: ['organization' => ['schedules', '{route:schedule}']])]
        public readonly int $serviceId,
    )
    {

    }
}