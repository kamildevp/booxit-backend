<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\DTO\WorkingHours\ScheduleAvailabilityGetDTO;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Enum\Weekday;
use App\Model\TimeWindow;
use App\Repository\ReservationRepository;
use App\Repository\ServiceRepository;
use App\Service\Utils\DateTimeUtils;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

class AvailabilityService
{
    public function __construct(
        private ReservationRepository $reservationRepository,
        private ServiceRepository $serviceRepository,
        private WorkingHoursService $workingHoursService,
        private DateTimeUtils $dateTimeUtils,
    )
    {
        
    }

    /** @return array<string, TimeWindow[]> */
    public function getScheduleAvailability(Schedule $schedule, ScheduleAvailabilityGetDTO $dto): array
    {
        $startDate = $this->dateTimeUtils->resolveDateTimeImmutableWithDefault($dto->dateFrom, new DateTimeImmutable('monday this week'))->setTime(0,0);
        $endDate = $this->dateTimeUtils->resolveDateTimeImmutableWithDefault($dto->dateTo, new DateTimeImmutable('sunday this week'))->setTime(23,59);
        $service = $dto->serviceId ? $this->serviceRepository->find($dto->serviceId): null;

        $weeklyWorkingHours = $this->workingHoursService->getScheduleWeeklyWorkingHours($schedule);
        $customWorkingHours = $this->workingHoursService->getScheduleCustomWorkingHours($schedule, $startDate, $endDate);
        $reservations = $this->reservationRepository->getScheduleReservations($schedule, $startDate, $endDate);

        return $this->buildAvailability(
            $startDate,
            $endDate,
            $weeklyWorkingHours,
            $customWorkingHours,
            $reservations,
            $service
        );
    }

    /**
     * @param array<string, TimeWindow[]> $weeklyWorkingHours
     * @param array<string, TimeWindow[]> $customWorkingHours
     * @param Reservation[] $reservations
     * @return array<string, TimeWindow[]>
     */
    private function buildAvailability(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        array $weeklyWorkingHours,
        array $customWorkingHours,
        array $reservations,
        ?Service $service
    ): array 
    {
        $result = [];
        $datePeriod = new DatePeriod($startDate, new DateInterval('P1D'), $endDate, DatePeriod::INCLUDE_END_DATE);

        foreach ($datePeriod as $date) {
            $dateString = $date->format('Y-m-d');
            $weekday = Weekday::createFromInt((int)$date->format('N'))->value;

            $workingHours = $customWorkingHours[$dateString] ?? $weeklyWorkingHours[$weekday];
            $availableTimeWindows = $this->buildAvailableTimeWindows($dateString, $workingHours, $reservations, $service);
            $result[$dateString] = array_values($availableTimeWindows);
        }

        return $result;
    }

    /**
     * @param TimeWindow[] $workingHours
     * @param Reservation[] $reservations
     * @return TimeWindow[]
     */
    private function buildAvailableTimeWindows(
        string $dateString,
        array $workingHours,
        array $reservations,
        ?Service $service
    ): array
    {
        $dateTimeWindows = array_map(
            fn($wh) => TimeWindow::createFromDateAndTime(
                $dateString,
                $wh->getStartTime(),
                $dateString,
                $wh->getEndTime()
            ),
            $workingHours
        );

        $dateReservations = array_filter($reservations, fn($r) => $r->getStartDateTime()->format('Y-m-d') === $dateString);
        $reservationsTimeWindows = array_map(fn($r) => new TimeWindow($r->getStartDateTime(), $r->getEndDateTime()), $dateReservations);

        $availableTimeWindows = $this->dateTimeUtils->timeWindowCollectionDiff($dateTimeWindows, $reservationsTimeWindows);

        if($service){
            $availableTimeWindows = array_filter(
                $availableTimeWindows,
                fn($tw) => $this->dateTimeUtils->compareDateIntervals($tw->getLength(), $service->getDuration()) > 0
            );
        }

        return array_values($availableTimeWindows);
    }
}