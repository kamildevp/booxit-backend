<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\Entity\Schedule;
use App\Entity\Service;
use App\Model\TimeWindow;
use App\Repository\ReservationRepository;
use App\Service\Utils\DateTimeUtils;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AvailabilityService
{
    private DateTimeZone $defaultTimezone;

    public function __construct(
        private ReservationRepository $reservationRepository,
        private WorkingHoursService $workingHoursService,
        private DateTimeUtils $dateTimeUtils,
        #[Autowire('%timezone%')]private string $defaultTimezoneString,
    )
    {
        $this->defaultTimezone = new DateTimeZone($defaultTimezoneString);
    }

    /** @return array<string, string[]> */
    public function getScheduleAvailability(
        Schedule $schedule, 
        Service $service, 
        DateTimeInterface $startDate, 
        DateTimeInterface $endDate,
        ?DateTimeZone $timezone = null
    ): array
    {
        $timezone = $timezone ?? $this->defaultTimezone;
        $startDate = DateTimeImmutable::createFromInterface($startDate)->setTime(0,0);
        $endDate = DateTimeImmutable::createFromInterface($endDate)->setTime(23,59);
        $earliestAvailabilityDateTime = $this->getEarliestAvailabilityDateTime($service)->setTimezone($timezone);
        $searchStartDate = $startDate < $earliestAvailabilityDateTime ? $earliestAvailabilityDateTime->setTime(0,0) : $startDate;
        $workingHours = $this->workingHoursService->getScheduleWorkingHoursForDateRange(
            $schedule, 
            $searchStartDate, 
            $endDate, 
            $timezone
        );
        $reservations = $this->reservationRepository->getActiveScheduleReservations(
            $schedule, 
            $searchStartDate->setTimezone($this->defaultTimezone), 
            $endDate->setTimezone($this->defaultTimezone)
        );

        $availableTimeWindows = $this->buildAvailableTimeWindows(
            $startDate,
            $endDate,
            $workingHours, 
            $reservations, 
            $service, 
            $earliestAvailabilityDateTime,
            $timezone
        );

        return $this->mapTimeWindowCollectionToDatesAvailability(
            $startDate,
            $endDate,
            $availableTimeWindows, 
            $service->getDuration(), 
            $schedule->getDivision(),
        );
    }

    /**
     * @param TimeWindow[] $workingHours
     * @param Reservation[] $reservations
     * @return TimeWindow[]
     */
    private function buildAvailableTimeWindows(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        array $workingHours,
        array $reservations,
        Service $service,
        DateTimeImmutable $earliestAvailabilityDateTime,
        DateTimeZone $timezone
    ): array
    {
        $availabilityBorders = $startDate < $earliestAvailabilityDateTime ? 
            [new TimeWindow($startDate, $earliestAvailabilityDateTime), new TimeWindow($endDate, $endDate->modify('+1 day'))] : 
            [new TimeWindow($endDate, $endDate->modify('+1 day'))];
        $workingHours = $this->dateTimeUtils->timeWindowCollectionDiff($workingHours, $availabilityBorders);
        $workingHours = $this->dateTimeUtils->mergeAdjacentTimeWindows($workingHours);

        $reservationsTimeWindows = array_map(fn($r) => (new TimeWindow($r->getStartDateTime(), $r->getEndDateTime()))->setTimezone($timezone), $reservations);
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
    private function mapTimeWindowCollectionToDatesAvailability(
        DateTimeImmutable $dateFrom, 
        DateTimeImmutable $dateTo, 
        array $collection, 
        DateInterval $minLength, 
        int $division
    )
    {
        $datePeriod = new DatePeriod($dateFrom, new DateInterval('P1D'), $dateTo);
        $mappedAvailability = array_fill_keys(
            array_map(fn($date) => $date->format('Y-m-d'), iterator_to_array($datePeriod)), 
            []
        );
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

    private function computeNextAvailabilityDateTime(DateTimeImmutable $dateTime, int $division): DateTimeImmutable
    {
        $rounded = DateTimeImmutable::createFromFormat('Y-m-d H:i', $dateTime->format('Y-m-d H:i'), $dateTime->getTimezone());
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