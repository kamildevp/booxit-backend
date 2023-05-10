<?php

namespace App\Service\SetterHelper\Task\Reservation;

use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Entity\TimeWindow;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use DateTime;

/** @property Reservation $object */
class TimeWindowTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function runPreValidation(string $startTime):void
    {
        $timeFormat = Schedule::TIME_FORMAT;
        $startTimeObject = DateTime::createFromFormat($timeFormat, $startTime);
        if(!$startTimeObject){
            $this->validationErrors['startTime'] = "Start time must be in format {$timeFormat}";
            return;
        }

        $service = $this->object->getService();
        if(!$service){
            return;
        }

        $duration = $service->getDuration();

        $startTimeObject->setDate(1970, 1, 1);

        $endTimeObject = (new DateTime)->setTimestamp($startTimeObject->getTimestamp())->add($duration);
        $timeWindow = new TimeWindow();
        $timeWindow->setStartTime($startTimeObject);
        $timeWindow->setEndTime($endTimeObject);
        
        $this->object->setTimeWindow($timeWindow);
    }




}