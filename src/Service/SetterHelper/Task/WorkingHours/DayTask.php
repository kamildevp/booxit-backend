<?php

namespace App\Service\SetterHelper\Task\WorkingHours;

use App\Entity\Schedule;
use App\Entity\WorkingHours;
use App\Exceptions\InvalidRequestException;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;

/** @property WorkingHours $object */
class DayTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function __construct(private SetterHelperInterface $setterHelper)
    {

    }

    public function runPreValidation(string $day)
    {
        if(!in_array($day, Schedule::WEEK_DAYS) && !(new DataHandlingHelper)->validateDateTime($day, Schedule::DATE_FORMAT)){
            $this->validationErrors['day'] = "Parameter is not valid date or day of week";
            return;
        }

        $workHours = $this->object;
        $workHoursDefined = $this->object->getSchedule()->getWorkingHours()->exists(function($key, $element) use ($workHours, $day){
            return $element != $workHours && $element->getDay() === $day;
        });

        if($workHoursDefined){
            $this->requestErrors['day'] = "Working hours for {$day} already defined";
            return;
        }

        $this->object->setDay($day);
    }
    
}