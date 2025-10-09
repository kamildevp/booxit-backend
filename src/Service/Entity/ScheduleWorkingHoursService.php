<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\DTO\WorkingHours\WeeklyWorkingHoursDTO;
use App\Entity\Schedule;
use App\Entity\WeekdayTimeWindow;
use App\Enum\Weekday;
use App\Repository\ScheduleRepository;
use DateTimeImmutable;

class ScheduleWorkingHoursService
{
    public function __construct(private ScheduleRepository $scheduleRepository)
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
}