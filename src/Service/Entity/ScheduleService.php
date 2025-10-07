<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\Entity\Schedule;
use App\Exceptions\ConflictException;
use App\Repository\ScheduleRepository;
use App\Repository\ServiceRepository;

class ScheduleService
{
    public function __construct(
        protected ScheduleRepository $scheduleRepository,
        protected ServiceRepository $serviceRepository,
    )
    {

    }

    public function addScheduleService(Schedule $schedule, int $serviceId): void
    {
        $service = $this->serviceRepository->findOrFail($serviceId);

        if($schedule->getOrganization() !== $service->getOrganization()){
            throw new ConflictException('This service belongs to different organization.');
        }

        if($schedule->hasService($service)){
            throw new ConflictException('This service is already assigned to this schedule.');
        }

        $schedule->addService($service);
        $this->scheduleRepository->save($schedule, true);
    }
}