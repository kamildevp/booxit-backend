<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\DTO\WorkingHours\CustomWorkingHoursUpdateDTO;
use App\DTO\WorkingHours\WeeklyWorkingHoursUpdateDTO;
use App\Entity\CustomTimeWindow;
use App\Entity\Schedule;
use App\Entity\WeekdayTimeWindow;
use App\Enum\Weekday;
use App\Model\TimeWindow;
use App\Repository\CustomTimeWindowRepository;
use App\Repository\ScheduleRepository;
use App\Service\Utils\DateTimeUtils;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class WorkingHoursService
{
    private DateTimeZone $defaultTimezone;

    public function __construct(
        private ScheduleRepository $scheduleRepository,
        private CustomTimeWindowRepository $customTimeWindowRepository,
        private DateTimeUtils $dateTimeUtils,
        #[Autowire('%timezone%')]private string $defaultTimezoneString,
    )
    {
        $this->defaultTimezone = new DateTimeZone($defaultTimezoneString);
    }

    public function setScheduleWeeklyWorkingHours(Schedule $schedule, WeeklyWorkingHoursUpdateDTO $dto): void
    {
        $scheduleWeekdayTimeWindows = $schedule->getWeekdayTimeWindows();
        $updatedTimeWindows = [];
        foreach(Weekday::values() as $weekday){
            foreach($dto->{$weekday} as $timeWindow){
                $weekdayTimeWindow = $scheduleWeekdayTimeWindows->findFirst(
                    fn($key, $element) => 
                        $element->getWeekDay() == $weekday && 
                        $element->getStartTime()->format('H:i') == $timeWindow->startTime && 
                        $element->getEndTime()->format('H:i') == $timeWindow->endTime
                );
                
                if(!$weekdayTimeWindow){
                    $weekdayTimeWindow = new WeekdayTimeWindow();
                    $weekdayTimeWindow->setWeekday($weekday);
                    $weekdayTimeWindow->setStartTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow->startTime));
                    $weekdayTimeWindow->setEndTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow->endTime));
                    $schedule->addWeekdayTimeWindow($weekdayTimeWindow);
                }

                $updatedTimeWindows[] = $weekdayTimeWindow;
            }
        }

        foreach($scheduleWeekdayTimeWindows as $weekdayTimeWindow){
            if(!in_array($weekdayTimeWindow, $updatedTimeWindows)){
                $schedule->removeWeekdayTimeWindow($weekdayTimeWindow);
            }
        }

        $this->scheduleRepository->save($schedule, true);
    }

    public function setScheduleCustomWorkingHours(Schedule $schedule, CustomWorkingHoursUpdateDTO $dto): void
    {
        $timezone = new DateTimeZone($dto->timezone);
        $startDate = DateTimeImmutable::createFromFormat('Y-m-d', $dto->date, $timezone)->setTime(0,0)->setTimezone($this->defaultTimezone);
        $endDate = DateTimeImmutable::createFromFormat('Y-m-d', $dto->date, $timezone)->setTime(23,59)->setTimezone($this->defaultTimezone);
        $scheduleCustomTimeWindows = $this->customTimeWindowRepository->getScheduleCustomTimeWindows($schedule, $startDate, $endDate);

        $updatedTimeWindows = [];
        foreach($dto->timeWindows as $timeWindow){
                $startDateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i', "$dto->date $timeWindow->startTime", $timezone)->setTimezone($this->defaultTimezone);
                $endDateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i', "$dto->date $timeWindow->endTime", $timezone)->setTimezone($this->defaultTimezone);
                $endDateTime = $startDateTime >= $endDateTime ? $endDateTime->modify('+1 day') : $endDateTime;

                $matchingCustomTimeWindows = array_filter($scheduleCustomTimeWindows,
                    fn($element) => 
                        $element->getStartDateTime() == $startDateTime && 
                        $element->getEndDateTime() == $endDateTime
                );
                
                if(count($matchingCustomTimeWindows) == 0){
                    $customTimeWindow = new CustomTimeWindow();
                    $customTimeWindow->setStartDateTime($startDateTime);
                    $customTimeWindow->setEndDateTime($endDateTime);
                    $customTimeWindow->setSchedule($schedule);
                    $this->customTimeWindowRepository->save($customTimeWindow);
                }
                else{
                    $customTimeWindow = reset($matchingCustomTimeWindows);
                }

                $updatedTimeWindows[] = $customTimeWindow;
        }

        foreach($scheduleCustomTimeWindows as $customTimeWindow){
            if(!in_array($customTimeWindow, $updatedTimeWindows)){
                $this->customTimeWindowRepository->remove($customTimeWindow);
            }
        }

        $this->customTimeWindowRepository->flush();
    }

    public function getScheduleCustomWorkingHours(Schedule $schedule, DateTimeInterface|string|null $startDate, DateTimeInterface|string|null $endDate, DateTimeZone|string $timezone): array
    {
        $timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
        $startDateTime = $this->dateTimeUtils
            ->resolveDateTimeImmutableWithDefault($startDate, new DateTimeImmutable('monday this week'), timezone: $timezone)
            ->setTime(0,0)
            ->setTimezone($this->defaultTimezone);
        $endDateTime = $this->dateTimeUtils
            ->resolveDateTimeImmutableWithDefault($endDate, new DateTimeImmutable('sunday this week'), timezone: $timezone)
            ->setTime(23,59)
            ->setTimezone($this->defaultTimezone);

        $customTimeWindows = $this->customTimeWindowRepository->getScheduleCustomTimeWindows($schedule, $startDateTime, $endDateTime);
        $customWorkingHours = [];
        foreach($customTimeWindows as $customTimeWindow){
            $timeWindow = new TimeWindow(
                $customTimeWindow->getStartDateTime()->setTimezone($timezone), 
                $customTimeWindow->getEndDateTime()->setTimezone($timezone)
            );

            $customWorkingHours[$timeWindow->getStartTime()->format('Y-m-d')][] = $timeWindow;
        }

        return $customWorkingHours;
    }

    public function getScheduleWeeklyWorkingHours(Schedule $schedule): array
    {
        /** @var WeekdayTimeWindow[] */
        $weekdayTimeWindows = $schedule->getWeekdayTimeWindows()->toArray();
        $weeklyWorkingHours = [];
        foreach(Weekday::values() as $weekday){
            $dayTimeWindows = array_filter($weekdayTimeWindows, fn($weekdayTimeWindow) => $weekdayTimeWindow->getWeekday() == $weekday);
            $dayTimeWindows = array_map(
                fn($weekdayTimeWindow) => new TimeWindow($weekdayTimeWindow->getStartTime(), $weekdayTimeWindow->getEndTime()), 
                array_values($dayTimeWindows)
            );

            $weeklyWorkingHours[$weekday] = $this->dateTimeUtils->sortTimeWindowCollection($dayTimeWindows);
        }

        return $weeklyWorkingHours;
    }
}