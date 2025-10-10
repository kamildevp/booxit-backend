<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\DTO\WorkingHours\CustomWorkingHoursDTO;
use App\DTO\WorkingHours\WeeklyWorkingHoursDTO;
use App\Entity\CustomTimeWindow;
use App\Entity\Schedule;
use App\Entity\WeekdayTimeWindow;
use App\Enum\Weekday;
use App\Repository\CustomTimeWindowRepository;
use App\Repository\ScheduleRepository;
use DateTimeImmutable;

class WorkingHoursService
{
    public function __construct(
        private ScheduleRepository $scheduleRepository,
        private CustomTimeWindowRepository $customTimeWindowRepository
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

    public function setScheduleCustomWorkingHours(Schedule $schedule, CustomWorkingHoursDTO $dto): void
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dto->date);
        $scheduleCustomTimeWindows = $this->customTimeWindowRepository->findBy(['schedule' => $schedule, 'date' => $date]);

        $updatedTimeWindows = [];
        foreach($dto->timeWindows as $timeWindow){
                $matchingCustomTimeWindows = array_filter($scheduleCustomTimeWindows,
                    fn($element) => 
                        $element->getStartTime()->format('H:i') == $timeWindow->startTime && 
                        $element->getEndTime()->format('H:i') == $timeWindow->endTime
                );
                
                if(count($matchingCustomTimeWindows) == 0){
                    $customTimeWindow = new CustomTimeWindow();
                    $customTimeWindow->setDate($date);
                    $customTimeWindow->setStartTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow->startTime));
                    $customTimeWindow->setEndTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow->endTime));
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
}