<?php

declare(strict_types=1);

namespace App\Service\Entity;

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
use DateTimeInterface;

class AvailabilityService
{
    public function __construct(
        private ReservationRepository $reservationRepository,
        private WorkingHoursService $workingHoursService,
        private DateTimeUtils $dateTimeUtils,
    )
    {

    }

    /** @return array<string, string[]> */
    public function getScheduleAvailability(
        Schedule $schedule, 
        Service $service, 
        DateTimeInterface $startDate, 
        DateTimeInterface $endDate
    ): array
    {
        $startDate = DateTimeImmutable::createFromInterface($startDate)->setTime(0,0);
        $endDate = DateTimeImmutable::createFromInterface($endDate)->setTime(23,59);
        $earliestAvailabilityDateTime = $this->getEarliestAvailabilityDateTime($service);
        $earliestAvailabilityDate = $earliestAvailabilityDateTime->setTime(0,0);
        $searchStartDate = $startDate < $earliestAvailabilityDate ? $earliestAvailabilityDate : $startDate;
        $weeklyWorkingHours = $this->workingHoursService->getScheduleWeeklyWorkingHours($schedule);
        $customWorkingHours = $this->workingHoursService->getScheduleCustomWorkingHours($schedule, $searchStartDate, $endDate);
        $reservations = $this->reservationRepository->getScheduleReservations($schedule, $searchStartDate, $endDate);

        return $this->buildAvailability(
            $startDate,
            $endDate,
            $weeklyWorkingHours,
            $customWorkingHours,
            $reservations,
            $schedule,
            $service,
            $earliestAvailabilityDateTime,
        );
    }

    /**
     * @param array<string, TimeWindow[]> $weeklyWorkingHours
     * @param array<string, TimeWindow[]> $customWorkingHours
     * @param Reservation[] $reservations
     * @return array<string, string[]>
     */
    private function buildAvailability(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        array $weeklyWorkingHours,
        array $customWorkingHours,
        array $reservations,
        Schedule $schedule,
        Service $service,
        DateTimeImmutable $earliestAvailabilityDateTime,
    ): array 
    {
        $mappedAvailability = [];
        $datePeriod = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);
        $workingHours = [];
        foreach ($datePeriod as $date) {
            $mappedAvailability[$date->format('Y-m-d')] = [];
            $dateWorkingHours = $this->getDateWorkingHours($weeklyWorkingHours, $customWorkingHours, $date, $earliestAvailabilityDateTime);
            $workingHours = array_merge($workingHours, $dateWorkingHours);
        }

        $availabilityBorders = $startDate < $earliestAvailabilityDateTime ? 
            [new TimeWindow($startDate, $earliestAvailabilityDateTime), new TimeWindow($endDate, $endDate->modify('+1 day'))] : 
            [new TimeWindow($endDate, $endDate->modify('+1 day'))];

        $workingHours = $this->dateTimeUtils->timeWindowCollectionDiff($workingHours, $availabilityBorders);
        $workingHours = $this->dateTimeUtils->mergeAdjacentTimeWindows($workingHours);
        $availableTimeWindows = $this->buildAvailableTimeWindows($workingHours, $reservations, $service);

        $mappedAvailability = array_merge(
            $mappedAvailability, 
            $this->mapTimeWindowCollectionToDatesAvailability(
                $availableTimeWindows, 
                $service->getDuration(), 
                $schedule->getDivision()
            )
        );

        return $mappedAvailability;
    }

    /** @return TimeWindow[] */
    private function getDateWorkingHours(        
        array $weeklyWorkingHours,
        array $customWorkingHours,
        DateTimeImmutable $date,
        DateTimeImmutable $earliestAvailabilityDateTime,
    ): array
    {
        if($date < $earliestAvailabilityDateTime){
            return [];
        }

        $dateString = $date->format('Y-m-d');
        $weekday = Weekday::createFromInt((int)$date->format('N'))->value;

        $workingHours = $customWorkingHours[$dateString] ?? $weeklyWorkingHours[$weekday];
        $dateTimeWindows = array_map(
            fn($wh) => TimeWindow::createFromDateAndTime(
                $dateString,
                $wh->getStartTime(),
                $dateString,
                $wh->getEndTime()
            ),
            $workingHours
        );

        return array_values($dateTimeWindows);
    }

    /**
     * @param TimeWindow[] $workingHours
     * @param Reservation[] $reservations
     * @return TimeWindow[]
     */
    private function buildAvailableTimeWindows(
        array $workingHours,
        array $reservations,
        Service $service
    ): array
    {
        $reservationsTimeWindows = array_map(fn($r) => new TimeWindow($r->getStartDateTime(), $r->getEndDateTime()), $reservations);
        $availableTimeWindows = $this->dateTimeUtils->timeWindowCollectionDiff($workingHours, $reservationsTimeWindows);

        $availableTimeWindows = array_filter(
            $availableTimeWindows,
            fn($tw) => $this->dateTimeUtils->compareDateIntervals($tw->getLength(), $service->getDuration()) >= 0
        );

        return $availableTimeWindows;
    }

    /**
     * @param TimeWindow[] $collection
     * @return array<string, string[]>
     */
    private function mapTimeWindowCollectionToDatesAvailability(array $collection, DateInterval $minLength, int $division)
    {
        $mappedAvailability = [];
        foreach($collection as $timeWindow){
            $currentStartTime = $this->computeNextAvailabilityDateTime($timeWindow->getStartTime(), $division);
            $maxStartTime = DateTimeImmutable::createFromInterface($timeWindow->getEndTime())->sub($minLength);

            while($currentStartTime <= $maxStartTime)
            {
                $mappedAvailability[$currentStartTime->format('Y-m-d')][] = $currentStartTime->format('H:i');
                $currentStartTime = $currentStartTime->modify("+{$division} minutes");
            }
        }

        return $mappedAvailability;
    }

    private function computeNextAvailabilityDateTime(DateTimeInterface $dateTime, int $division): DateTimeImmutable
    {
        $rounded = DateTimeImmutable::createFromFormat('Y-m-d H:i', $dateTime->format('Y-m-d H:i'));
        $rounded = $rounded < $dateTime ? $rounded->modify('+1 minute') : $rounded;
        $minutes = (int)$rounded->format('i');
        $addMinutes = ($division - ($minutes % $division)) % $division;
        
        return $rounded->modify("+{$addMinutes} minutes");
    }

    private function getEarliestAvailabilityDateTime(Service $service)
    {
        $offsetInMinutes = $service->getAvailabilityOffset();
        return (new DateTimeImmutable())->modify("+{$offsetInMinutes} minutes");
    }
}