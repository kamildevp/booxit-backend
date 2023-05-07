<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\Schedule;
use App\Entity\WorkingHours;
use App\Exceptions\InvalidRequestException;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;

/** @property WorkingHours $object */
class WorkingHoursDayTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function __construct(private SetterHelperInterface $setterHelper)
    {

    }

    public function runPreValidation(string $day)
    {
        if(!in_array($day, Schedule::WEEK_DAYS) && !(new DataHandlingHelper)->validateDateTime($day, Schedule::DATE_FORMAT)){
            throw new InvalidRequestException("Parameter {$day} is not valid date or day of week");
        }

        $workHours = $this->object;
        $workHoursDefined = $this->object->getSchedule()->getWorkingHours()->exists(function($key, $element) use ($workHours, $day){
            return $element != $workHours && $element->getDay() === $day;
        });

        if($workHoursDefined){
            throw new InvalidRequestException("Working hours for {$day} already defined");
        }

        $this->object->setDay($day);
    }
    
}