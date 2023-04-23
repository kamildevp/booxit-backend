<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Entity\TimeWindow;
use App\Entity\WorkingHours;
use App\Exceptions\InvalidRequestException;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use DateTime;
use Doctrine\Common\Collections\Collection;

/** @property Schedule $object */
class WorkingHoursTask implements SetterTaskInterface
{
    use SetterTaskTrait;
    const WEEK_DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public function __construct(private SetterHelperInterface $setterHelper)
    {

    }

    public function runPreValidation(array $workingHours)
    {
        foreach($workingHours as $day => $timeWindows){
            if(!in_array($day, self::WEEK_DAYS) && !(new DataHandlingHelper)->validateDateTime($day, Schedule::DATE_FORMAT)){
                throw new InvalidRequestException("Parameter {$day} is not valid date or day of week");
            }
            
            $workHours = $this->object->getDayWorkingHours($day);
            if($timeWindows === null){
                if($workHours instanceof WorkingHours){
                    $this->object->removeWorkingHours($workHours);
                }
                continue;
            }

            if(!($workHours instanceof WorkingHours)){
                $workHours = new WorkingHours();
                $workHours->setDay($day);
                $this->object->addWorkingHours($workHours);
            }

            
            $definedTimeWindows = $workHours->getTimeWindows();
            $timeWindowsDiff = count($definedTimeWindows) - count($timeWindows);

            for($i=0; $i<$timeWindowsDiff; $i++){
                $definedTimeWindows->removeElement($definedTimeWindows->last());
            }

            $loop_indx = 0;
            foreach($timeWindows as $timeWindowSettings){
                $timeWindow = $definedTimeWindows[$loop_indx] ?? new TimeWindow();
                $this->setterHelper->updateObjectSettings($timeWindow, $timeWindowSettings);
                $workHours->addTimeWindow($timeWindow);
                $loop_indx++;
            }

            $this->validateTimeWindows($day ,$workHours->getTimeWindows());
            $this->validateReservationsConflicts($workHours, $workHours->getSchedule()->getReservations());
        }
    }

    /** @param Collection<int, TimeWindow>  $timeWindows*/
    private function validateTimeWindows(string $day, Collection $timeWindows){
        
        foreach($timeWindows as $timeWindow){
            $timeWindowsOverlay = $timeWindows->exists(function($key, $element) use ($timeWindow){
                $timeWindowStart = $timeWindow->getStartTime();
                $timeWindowEnd = $timeWindow->getEndTime();
                $elementStart = $element->getStartTime();
                $elementEnd = $element->getEndTime();

                return $element != $timeWindow  && (
                    ($elementStart <= $timeWindowStart && $timeWindowStart < $elementEnd) || 
                    ($elementEnd < $timeWindowEnd  && $timeWindowEnd <= $elementEnd)
                );
            });

            if($timeWindowsOverlay){
                throw new InvalidRequestException("Time windows defined for {$day} are overlaping");
            }
        }
    }

    /** @param Collection<int, Reservation>  $reservations*/
    private function validateReservationsConflicts(WorkingHours $workHours, Collection $reservations){
        $workDay = $workHours->getDay();
        $workTimeWindows = $workHours->getTimeWindows();

        $filtredReservations = $reservations->filter(function($reservation) use ($workDay){
            $reservationDate = $reservation->getDate();
            $reservationEndTime = $reservation->getTimeWindow()->getEndTime();
            $reservationDateTime = DateTime::createFromFormat(Schedule::DATE_FORMAT, $reservationDate);
            $reservationDateTime->setTime((int)$reservationEndTime->format('H'), (int)$reservationEndTime->format('i'), (int)$reservationEndTime->format('s'));
            if($reservationDateTime < new DateTime('now')){
                return false;
            }

            $reservationDay = in_array($workDay, self::WEEK_DAYS) ? self::WEEK_DAYS[$reservationDateTime->format('N')-1] : $reservationDate;
            return $reservationDay === $workDay;
        });
        
        foreach($filtredReservations as $reservation){
            $reservedTimeWindow = $reservation->getTimeWindow();
            $reservationMatch = $workTimeWindows->exists(function($key, $timeWindow) use ($reservedTimeWindow){
                return $reservedTimeWindow->getStartTime() >= $timeWindow->getStartTime() && $reservedTimeWindow->getEndTime() <= $timeWindow->getEndTime();
            });

            if(!$reservationMatch){
                $reservationDate = $reservation->getDate();
                throw new InvalidRequestException("Cannot modify working hours for {$workDay} because of reservation conflict on {$reservationDate}");
            }
        }
    }
}