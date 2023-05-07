<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\ScheduleAssignment;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\Trait\SetterTaskTrait;

/** @property ScheduleAssignment $object */
class ScheduleAssignmentMemberTask implements SetterTaskInterface
{
    use SetterTaskTrait;
    const ACCESS_TYPES = ['READ', 'WRITE'];

    public function runPreValidation(int $memberId)
    {   
        $organizationMembers = $this->object->getSchedule()->getOrganization()->getMembers();

        $member = $organizationMembers->findFirst(function($key, $element) use ($memberId){
            return $element->getId() === $memberId;
        });

        if(!$member){
            throw new InvalidRequestException("Cannot find organization member with id = {$memberId}");
        }

        $assignment = $this->object;
        $memberAssigned = $this->object->getSchedule()->getAssignments()->exists(function($key, $element) use ($assignment, $member){
            return $element != $assignment && $element->getOrganizationMember() == $member;
        });

        if($memberAssigned){
            throw new InvalidRequestException("Member with id = {$memberId} is already assigned to schedule");
        }

        $this->object->setOrganizationMember($member);
    }



    

}