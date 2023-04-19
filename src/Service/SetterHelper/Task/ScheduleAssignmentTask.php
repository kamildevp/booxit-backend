<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\ScheduleAssignment;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\Trait\SetterTaskTrait;

/** @property ScheduleAssignment $object */
class ScheduleAssignmentTask implements SetterTaskInterface
{
    use SetterTaskTrait;
    const ACCESS_TYPES = ['READ', 'WRITE'];

    public function runPreValidation(int $memberId, string $accessType)
    {   
        $organizationMembers = $this->object->getSchedule()->getOrganization()->getMembers();

        $member = $organizationMembers->findFirst(function($key, $element) use ($memberId){
            return $element->getId() === $memberId;
        });

        if(!$member){
            throw new InvalidRequestException("Cannot find organization member with id = {$memberId}");
        }

        if(!in_array($accessType , self::ACCESS_TYPES)){
            $accessTypesString = join(', ', self::ACCESS_TYPES);
            throw new InvalidRequestException("Invalid access type '{$accessType}'. Allowed access types: {$accessTypesString}");
        }

        $this->object->setOrganizationMember($member);
        $this->object->setAccessType($accessType);
    }



    

}