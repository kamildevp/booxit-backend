<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\ScheduleAssignment;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\Trait\SetterTaskTrait;

/** @property ScheduleAssignment $object */
class ScheduleAssignmentAccessTypeTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function runPreValidation(string $accessType)
    {   
        if(!in_array($accessType , ScheduleAssignment::ACCESS_TYPES)){
            $accessTypesString = join(', ', ScheduleAssignment::ACCESS_TYPES);
            throw new InvalidRequestException("Invalid access type '{$accessType}'. Allowed access types: {$accessTypesString}");
        }

        $this->object->setAccessType($accessType);
    }



    

}