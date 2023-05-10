<?php

namespace App\Service\SetterHelper\Task\ScheduleAssignment;

use App\Entity\ScheduleAssignment;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;

/** @property ScheduleAssignment $object */
class AccessTypeTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function runPreValidation(string $accessType)
    {   
        if(!in_array($accessType , ScheduleAssignment::ACCESS_TYPES)){
            $accessTypesString = join(', ', ScheduleAssignment::ACCESS_TYPES);
            $this->validationErrors['accessType'] = "Invalid access type. Allowed access types: {$accessTypesString}";
            return;
        }

        $this->object->setAccessType($accessType);
    }



    

}