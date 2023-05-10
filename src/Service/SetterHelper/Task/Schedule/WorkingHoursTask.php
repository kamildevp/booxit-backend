<?php

namespace App\Service\SetterHelper\Task\Schedule;

use App\Entity\Schedule;
use App\Entity\WorkingHours;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/** @property Schedule $object */
class WorkingHoursTask implements SetterTaskInterface
{
    use SetterTaskTrait;
    const MODIFICATION_TYPES = ['ADD', 'REMOVE', 'PATCH', 'OVERWRITE'];

    public function __construct(private SetterHelperInterface $setterHelper)
    {

    }

    public function runPreValidation(array $workingHours, string $modificationType)
    {
        if(!in_array($modificationType, self::MODIFICATION_TYPES)){
            $modificationTypesString = join(', ', self::MODIFICATION_TYPES);
            $this->validationErrors['modificationType'] = "Invalid modification type. Allowed modifications types: {$modificationTypesString}";
            return;
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
                $this->requestErrors['workingHours'] = "Parameter must be array of strings (days or dates)";
                return;
            }

            $workHours = $this->object->getWorkingHours()->findFirst(function($key, $element) use ($day){
                return $element->getDay() === $day;
            });

            if(!$workHours){
                $this->requestErrors['workingHours'][] = "Working hours for {$day} are not defined";
                continue;
            }

            $workHours->getTimeWindows()->clear();                
            $error = $this->validateReservationsConflicts($workHours->getDay(), $this->object->getReservations());
            if(!is_null($error)){
                $this->validationErrors['workingHours'][] = $error;
                continue;
            }
            $this->object->removeWorkingHours($workHours);
        }
    }

    public function addWorkingHours(array $workingHours)
    {
        $workHours = new WorkingHours();
        $loopIndx = 0;   
        foreach($workingHours as $settings)
        {
            if(!is_array($settings)){
                $this->requestErrors['workingHours'][$loopIndx] = "Parameter must be array of working hours settings";
                $loopIndx++;
                continue;
            }

            $this->object->addWorkingHours($workHours);
            try{
                $this->setterHelper->updateObjectSettings($workHours, $settings, ['Default']);
            }
            catch(InvalidRequestException){
                $this->requestErrors['workingHours'][$loopIndx] = $this->setterHelper->getRequestErrors();
                $loopIndx++;
                continue;
            }

            $validationErrors = $this->setterHelper->getValidationErrors();
            if(!empty($validationErrors)){
                $this->validationErrors['workingHours'][$loopIndx] = $this->setterHelper->getValidationErrors();
            }
            $loopIndx++;
        }
    }

    public function patchWorkingHours(array $workingHours)
    {   
        $loopIndx = 0;
        foreach($workingHours as $settings)
        {
            if(!is_array($settings)){
                $this->requestErrors['workingHours'][$loopIndx] = "Parameter must be array of working hours settings";
                $loopIndx++;
                continue;
            }

            if(!array_key_exists('day', $settings)){
                $this->requestErrors['workingHours'][$loopIndx]['day'] = 'Parameter is required';
                $loopIndx++;
                continue;
            }

            $day = $settings['day'];

            $workHours = $this->object->getWorkingHours()->findFirst(function($key, $element) use ($day){
                return $element->getDay() === $day;
            });

            if(!$workHours){
                $this->requestErrors['workingHours'][$loopIndx]['day'] = "Working hours for {$day} are not defined";
                $loopIndx++;
                continue;
            }

            try{
                $this->setterHelper->updateObjectSettings($workHours, $settings);
            }
            catch(InvalidRequestException){
                $this->requestErrors['workingHours'][$loopIndx] = $this->setterHelper->getRequestErrors();
                $loopIndx++;
                continue;
            }

            $validationErrors = $this->setterHelper->getValidationErrors();
            if(!empty($validationErrors)){
                $this->validationErrors['workingHours'][$loopIndx] = $this->setterHelper->getValidationErrors();
                $loopIndx++;
                continue;
            }
            
            $error = $this->validateReservationsConflicts($workHours->getDay(), $this->object->getReservations());
            if(!is_null($error)){
                $this->validationErrors['workingHours'][$loopIndx] = $error;
            }
            
            $loopIndx++;
        }
    }

    public function overwriteWorkingHours(array $workingHours)
    {   
        $workingHoursCollection = new ArrayCollection($this->object->getWorkingHours()->getValues());
        $this->object->getWorkingHours()->clear();

        $loopIndx = 0;
        foreach($workingHours as $settings)
        {
            if(!is_array($settings)){
                $this->requestErrors['workingHours'][$loopIndx] = "Parameter must be array of working hours settings";
                $loopIndx++;
                continue;
            }

            if(!array_key_exists('day', $settings)){
                $this->requestErrors['workingHours'][$loopIndx]['day'] = 'Parameter is required';
                $loopIndx++;
                continue;
            }

            $day = $settings['day'];

            $workHours = $workingHoursCollection->findFirst(function($key, $element) use ($day){
                return $element->getDay() === $day;
            }) ?? new WorkingHours();

            $this->object->addWorkingHours($workHours);

            try{
                $this->setterHelper->updateObjectSettings($workHours, $settings, ['Default']);
            }
            catch(InvalidRequestException){
                $this->requestErrors['workingHours'][$loopIndx] = $this->setterHelper->getRequestErrors();
                $loopIndx++;
                continue;
            }

            $validationErrors = $this->setterHelper->getValidationErrors();
            if(!empty($validationErrors)){
                $this->validationErrors['workingHours'][$loopIndx] = $this->setterHelper->getValidationErrors();
            }
            $loopIndx++;
        }

        if(!empty($this->validationErrors)){
            return;
        }

        foreach($workingHoursCollection as $workingHours){
            $error = $this->validateReservationsConflicts($workingHours->getDay(), $this->object->getReservations());
            if(!is_null($error)){
                $this->validationErrors['workingHours'][] = $error;
            }
        }
    }

    /** @param Collection<int, Reservation>  $reservations*/
    private function validateReservationsConflicts(string $workDay, Collection $reservations){
        $workingHours = $this->object->getDayWorkingHours($workDay);
        $workTimeWindows = $workingHours ? $workingHours->getTimeWindows() : new ArrayCollection();

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
                return "Cannot modify working hours for {$workDay} because of reservation conflict on {$reservationDate}";
            }
        }

        return null;
    }
}