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
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class WorkingHoursService
{
    public function __construct(
        private ScheduleRepository $scheduleRepository,
        private CustomTimeWindowRepository $customTimeWindowRepository,
        private DateTimeUtils $dateTimeUtils,
        #[Autowire('%timezone%')]private string $defaultTimezone,
    )
    {
        
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
                        $element->getEndTime()->format('H:i') == $timeWindow->endTime &&
                        $element->getTimezone() == $dto->timezone
                );
                
                if(!$weekdayTimeWindow){
                    $weekdayTimeWindow = new WeekdayTimeWindow();
                    $weekdayTimeWindow->setWeekday($weekday);
                    $weekdayTimeWindow->setStartTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow->startTime));
                    $weekdayTimeWindow->setEndTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow->endTime));
                    $weekdayTimeWindow->setTimezone($dto->timezone);
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
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dto->date);
        $scheduleCustomTimeWindows = $this->customTimeWindowRepository->findBy(['schedule' => $schedule, 'date' => $date]);

        $updatedTimeWindows = [];
        foreach($dto->timeWindows as $timeWindow){
                $matchingCustomTimeWindows = array_filter($scheduleCustomTimeWindows,
                    fn($element) => 
                        $element->getStartTime()->format('H:i') == $timeWindow->startTime && 
                        $element->getEndTime()->format('H:i') == $timeWindow->endTime &&
                        $element->getTimezone() == $dto->timezone
                );
                
                if(count($matchingCustomTimeWindows) == 0){
                    $customTimeWindow = new CustomTimeWindow();
                    $customTimeWindow->setDate($date);
                    $customTimeWindow->setStartTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow->startTime));
                    $customTimeWindow->setEndTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow->endTime));
                    $customTimeWindow->setSchedule($schedule);
                    $customTimeWindow->setTimezone($dto->timezone);
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

    public function getScheduleCustomWorkingHours(Schedule $schedule, DateTimeInterface|string|null $startDate, DateTimeInterface|string|null $endDate): array
    {
        $startDate = $this->dateTimeUtils->resolveDateTimeImmutableWithDefault($startDate, new DateTimeImmutable('monday this week'));
        $endDate = $this->dateTimeUtils->resolveDateTimeImmutableWithDefault($endDate, new DateTimeImmutable('sunday this week'));

        $customTimeWindows = $this->customTimeWindowRepository->getScheduleCustomTimeWindows($schedule, $startDate, $endDate);
        $customWorkingHours = [];
        foreach($customTimeWindows as $customTimeWindow){
            $dateString = $customTimeWindow->getDate()->format('Y-m-d');
            $customWorkingHours[$dateString]['time_windows'][] = new TimeWindow($customTimeWindow->getStartTime(), $customTimeWindow->getEndTime());
            $customWorkingHours[$dateString]['timezone'] = $customTimeWindow->getTimezone();
        }

        return $customWorkingHours;
    }

    public function getScheduleWeeklyWorkingHours(Schedule $schedule): array
    {
        /** @var WeekdayTimeWindow[] */
        $weekdayTimeWindows = $schedule->getWeekdayTimeWindows()->toArray();
        $firstWindow = $schedule->getWeekdayTimeWindows()->first();
        $timezone = $firstWindow !== false ? $firstWindow->getTimezone() : $this->defaultTimezone;
        
        $weeklyWorkingHours = [];
        foreach(Weekday::values() as $weekday){
            $dayTimeWindows = array_filter($weekdayTimeWindows, fn($weekdayTimeWindow) => $weekdayTimeWindow->getWeekday() == $weekday);
            $dayTimeWindows = array_map(
                fn($weekdayTimeWindow) => new TimeWindow($weekdayTimeWindow->getStartTime(), $weekdayTimeWindow->getEndTime()), 
                array_values($dayTimeWindows)
            );

            $weeklyWorkingHours[$weekday] = $this->dateTimeUtils->sortTimeWindowCollection($dayTimeWindows);
        }

        $weeklyWorkingHours['timezone'] = $timezone;

        return $weeklyWorkingHours;
    }
}