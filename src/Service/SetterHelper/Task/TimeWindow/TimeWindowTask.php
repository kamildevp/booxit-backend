<?php

namespace App\Service\SetterHelper\Task\TimeWindow;

use App\Entity\Schedule;
use App\Entity\TimeWindow;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use DateTime;

/** @property TimeWindow $object */
class TimeWindowTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function runPreValidation(string $startTime, string $endTime)
    {
        $timeFormat = Schedule::TIME_FORMAT;

        $startTimeObject = DateTime::createFromFormat($timeFormat, $startTime);
        if(!($startTimeObject && $startTimeObject->format($timeFormat) === $startTime)){
            $this->validationErrors['startTime'] =("Value is not valid time format. Supported time format: {$timeFormat}");
        }
        
        $endTimeObject = DateTime::createFromFormat($timeFormat, $endTime);
        if(!($endTimeObject && $endTimeObject->format($timeFormat) === $endTime)){
            $this->validationErrors['endTime'] =("Value is not valid time format. Supported time format: {$timeFormat}");
        }

        if(!empty($this->validationErrors)){
            return;
        }

        $startTimeObject->setDate(1970, 1, 1);
        $endTimeObject->setDate(1970, 1, 1);

        if($startTimeObject >= $endTimeObject){
            $this->validationErrors['startTime'] = "Time window start time({$startTime}) must be defined at earlier time than end time({$endTime})";
            return;
        }

        $this->object->setStartTime($startTimeObject);
        $this->object->setEndTime($endTimeObject);
    }

}