<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\Schedule;
use App\Entity\WorkingHours;
use App\Exceptions\InvalidRequestException;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/** @property Schedule $object */
class ScheduleWorkingHoursTask implements SetterTaskInterface
{
    use SetterTaskTrait;
    const MODIFICATION_TYPES = ['ADD', 'REMOVE', 'PATCH', 'OVERWRITE'];

    public function __construct(private SetterHelperInterface $setterHelper)
    {

    }

    public function runPreValidation(array $workingHours, string $modificationType)
    {
        if(!in_array($modificationType, self::MODIFICATION_TYPES)){
            throw new InvalidRequestException('Invalid modification type. Allowed modifications types: ADD, PATCH, REMOVE, OVERWRITE');
        }

        switch($modificationType){
            case 'REMOVE':
                $this->removeWorkingHours($workingHours);
                break;
            case 'ADD':
                $this->addWorkingHours($workingHours);
                break;
            case 'PATCH':
                $this->patchWorkingHours($workingHours);
                break;
            case 'OVERWRITE':
                $this->overwriteWorkingHours($workingHours);
                break;
        }
    }

    public function removeWorkingHours(array $workingHours)
    {
        $workHours = new WorkingHours();   
        foreach($workingHours as $day)
        {
            if(!is_string($day)){
                throw new InvalidRequestException("Parameter working_hours must be array of strings (days or dates)");
            }

            $workHours = $this->object->getWorkingHours()->findFirst(function($key, $element) use ($day){
                return $element->getDay() === $day;
            });

            if(!$workHours){
                throw new InvalidRequestException("Working hours for {$day} are not defined");
            }

            $workHours->getTimeWindows()->clear();                
            $this->validateReservationsConflicts($workHours, $this->object->getReservations());
            $this->object->removeWorkingHours($workHours);
        }
    }

    public function addWorkingHours(array $workingHours)
    {
        $workHours = new WorkingHours();   
        foreach($workingHours as $settings)
        {
            if(!is_array($settings)){
                throw new InvalidRequestException("Parameter working_hours must be array of settings arrays");
            }

            $this->object->addWorkingHours($workHours);
            $this->setterHelper->updateObjectSettings($workHours, $settings, ['Default']);
        }
    }

    public function patchWorkingHours(array $workingHours)
    {   
        foreach($workingHours as $settings)
        {
            if(!is_array($settings)){
                throw new InvalidRequestException("Parameter working_hours must be array of settings arrays");
            }

            if(!array_key_exists('day', $settings)){
                throw new InvalidRequestException('Parameter day is required');
            }

            $day = $settings['day'];

            $workHours = $this->object->getWorkingHours()->findFirst(function($key, $element) use ($day){
                return $element->getDay() === $day;
            });

            if(!$workHours){
                throw new InvalidRequestException("Working hours for {$day} are not defined");
            }

            $this->setterHelper->updateObjectSettings($workHours, $settings);
            $this->validateReservationsConflicts($workHours, $this->object->getReservations());
        }
    }

    public function overwriteWorkingHours(array $workingHours)
    {   
        $workingHoursCollection = new ArrayCollection($this->object->getWorkingHours()->getValues());
        $this->object->clearWorkingHours();

        foreach($workingHours as $settings)
        {
            if(!is_array($settings)){
                throw new InvalidRequestException("Parameter working_hours must be array of settings arrays");
            }

            if(!array_key_exists('day', $settings)){
                throw new InvalidRequestException('Parameter day is required');
            }

            $day = $settings['day'];

            $workHours = $workingHoursCollection->findFirst(function($key, $element) use ($day){
                return $element->getDay() === $day;
            }) ?? new WorkingHours();

            $this->object->addWorkingHours($workHours);
            $this->setterHelper->updateObjectSettings($workHours, $settings, ['Default']);
            $this->validateReservationsConflicts($workHours, $this->object->getReservations());
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

            $reservationDay = in_array($workDay, Schedule::WEEK_DAYS) ? Schedule::WEEK_DAYS[$reservationDateTime->format('N')-1] : $reservationDate;
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