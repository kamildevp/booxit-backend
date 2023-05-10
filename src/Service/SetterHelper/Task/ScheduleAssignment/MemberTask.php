<?php

namespace App\Service\SetterHelper\Task\ScheduleAssignment;

use App\Entity\ScheduleAssignment;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;

/** @property ScheduleAssignment $object */
class MemberTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function runPreValidation(int $memberId)
    {   
        $organizationMembers = $this->object->getSchedule()->getOrganization()->getMembers();

        $member = $organizationMembers->findFirst(function($key, $element) use ($memberId){
            return $element->getId() === $memberId;
        });

        if(!$member){
            $this->requestErrors['memberId'] = "Member with id = {$memberId} does not exist";
            return;
        }

        $assignment = $this->object;
        $memberAssigned = $this->object->getSchedule()->getAssignments()->exists(function($key, $element) use ($assignment, $member){
            return $element != $assignment && $element->getOrganizationMember() == $member;
        });

        if($memberAssigned){
            $this->requestErrors['memberId'] = "Member with id = {$memberId} is already assigned to schedule";
            return;
        }

        $this->object->setOrganizationMember($member);
    }



    

}