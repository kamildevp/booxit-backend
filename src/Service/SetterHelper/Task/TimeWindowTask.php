<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\TimeWindow;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use DateTime;

/** @property TimeWindow $object */
class TimeWindowTask implements SetterTaskInterface
{
    use SetterTaskTrait;


    public function runPreValidation(string $startTime, string $endTime)
    {
        $startTimeObject = $this->getDateTimeObject($startTime);
        $endTimeObject = $this->getDateTimeObject($endTime);

        if($startTimeObject >= $endTimeObject){
            throw new InvalidRequestException("Time window start time({$startTime}) must be defined at earlier time than end time({$endTime})");
        }
        $this->object->setStartTime($startTimeObject);
        $this->object->setEndTime($endTimeObject);
    }

    private function getDateTimeObject(string $dateTime, $format = 'H:i:s')
    {
        $dateTimeObject = DateTime::createFromFormat($format, $dateTime);

        if(!($dateTimeObject && $dateTimeObject->format($format) === $dateTime)){
            throw new InvalidRequestException("{$dateTime} is not valid time format");
        }
        $dateTimeObject->setDate(1970, 1, 1);

        return $dateTimeObject;
    }

}