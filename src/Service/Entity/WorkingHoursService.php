<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\DTO\WorkingHours\DateWorkingHoursDTO;
use App\DTO\WorkingHours\WeeklyWorkingHoursDTO;
use App\Entity\DateTimeWindow;
use App\Entity\Schedule;
use App\Entity\WeekdayTimeWindow;
use App\Enum\Weekday;
use App\Repository\DateTimeWindowRepository;
use App\Repository\ScheduleRepository;
use DateTimeImmutable;

class WorkingHoursService
{
    public function __construct(
        private ScheduleRepository $scheduleRepository,
        private DateTimeWindowRepository $dateTimeWindowRepository
    )
    {
        
    }

    public function setScheduleWeeklyWorkingHours(Schedule $schedule, WeeklyWorkingHoursDTO $dto): void
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

    public function setScheduleDateWorkingHours(Schedule $schedule, DateWorkingHoursDTO $dto): void
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dto->date);
        $scheduleDateTimeWindows = $this->dateTimeWindowRepository->findBy(['schedule' => $schedule, 'date' => $date]);

        $updatedTimeWindows = [];
        foreach($dto->timeWindows as $timeWindow){
                $matchingDateTimeWindows = array_filter($scheduleDateTimeWindows,
                    fn($element) => 
                        $element->getStartTime()->format('H:i') == $timeWindow->startTime && 
                        $element->getEndTime()->format('H:i') == $timeWindow->endTime
                );
                
                if(count($matchingDateTimeWindows) == 0){
                    $dateTimeWindow = new DateTimeWindow();
                    $dateTimeWindow->setDate($date);
                    $dateTimeWindow->setStartTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow->startTime));
                    $dateTimeWindow->setEndTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow->endTime));
                    $dateTimeWindow->setSchedule($schedule);
                    $this->dateTimeWindowRepository->save($dateTimeWindow);
                }
                else{
                    $dateTimeWindow = reset($matchingDateTimeWindows);
                }

                $updatedTimeWindows[] = $dateTimeWindow;
        }

        foreach($scheduleDateTimeWindows as $dateTimeWindow){
            if(!in_array($dateTimeWindow, $updatedTimeWindows)){
                $this->dateTimeWindowRepository->remove($dateTimeWindow);
            }
        }

        $this->dateTimeWindowRepository->flush();
    }
}