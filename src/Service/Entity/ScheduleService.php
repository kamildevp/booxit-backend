<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\DTO\ScheduleService\ScheduleServiceAvailabilityGetDTO;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Exceptions\ConflictException;
use App\Exceptions\EntityNotFoundException;
use App\Repository\ScheduleRepository;
use App\Repository\ServiceRepository;
use App\Service\Utils\DateTimeUtils;
use DateTimeImmutable;

class ScheduleService
{
    public function __construct(
        protected ScheduleRepository $scheduleRepository,
        protected ServiceRepository $serviceRepository,
        protected DateTimeUtils $dateTimeUtils,
        protected AvailabilityService $availabilityService,
    )
    {

    }

    public function addScheduleService(Schedule $schedule, int $serviceId): void
    {
        $service = $this->serviceRepository->findOrFail($serviceId);

        if($schedule->hasService($service)){
            throw new ConflictException('This service is already assigned to this schedule.');
        }

        $schedule->addService($service);
        $this->scheduleRepository->save($schedule, true);
    }

    public function removeScheduleService(Schedule $schedule, Service $service): void
    {
        if(!$schedule->hasService($service)){
            throw new EntityNotFoundException(Service::class);
        }
        
        $schedule->removeService($service);
        $this->scheduleRepository->save($schedule, true);
    }

    /** @return array<string, string[]> */
    public function getScheduleAvailability(Schedule $schedule, Service $service, ScheduleServiceAvailabilityGetDTO $dto): array
    {
        if(!$schedule->hasService($service)){
            throw new EntityNotFoundException(Service::class);
        }

        $startDate = $this->dateTimeUtils->resolveDateTimeImmutableWithDefault($dto->dateFrom, new DateTimeImmutable('monday this week'));
        $endDate = $this->dateTimeUtils->resolveDateTimeImmutableWithDefault($dto->dateTo, new DateTimeImmutable('sunday this week'));

        return $this->availabilityService->getScheduleAvailability($schedule, $service, $startDate, $endDate);
    }
}