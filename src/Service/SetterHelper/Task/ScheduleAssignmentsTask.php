<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\Schedule;
use App\Entity\ScheduleAssignment;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\Common\Collections\Collection;

/** @property Schedule $object */
class ScheduleAssignmentsTask implements SetterTaskInterface
{
    use SetterTaskTrait;
    const MODIFICATION_TYPES = ['ADD', 'REMOVE', 'OVERWRITE'];

    public function __construct(private SetterHelperInterface $setterHelper)
    {

    }

    public function runPreValidation(array $assignments, string $assignmentsModificationType = 'ADD')
    {
        if(!in_array($assignmentsModificationType , self::MODIFICATION_TYPES)){
            $modificationTypesString = join(', ', self::MODIFICATION_TYPES);
            throw new InvalidRequestException("Invalid assignments modification type. Allowed modification types: {$modificationTypesString}");
        }

        $scheduleAssignments = $this->object->getAssignments();

        if($assignmentsModificationType === 'REMOVE'){
            $this->removeAssignments($scheduleAssignments, $assignments);
        }
        else{
            $this->newAssignments($scheduleAssignments, $assignments, $assignmentsModificationType);
        }
    }

    private function removeAssignments(Collection $scheduleAssignments, array $assignments){
        foreach($assignments as $assignmentId){
            if(!is_int($assignmentId)){
                throw new InvalidRequestException("Assigments must be array of integers");
            }

            $assignment = $scheduleAssignments->findFirst(function($key, $element) use ($assignmentId){
                return $element->getId() === $assignmentId;
            });
            if(is_null($assignment)){
                throw new InvalidRequestException("Cannot find assignment with id = {$assignmentId} to remove");
            }

            $scheduleAssignments->removeElement($assignment);
        }
    }

    private function newAssignments(Collection $scheduleAssignments, array $assignments, string $modificationType){
        if($modificationType === 'OVERWRITE'){
            $assignmentsDiff = count($scheduleAssignments) - count($assignments);
            for($i=0; $i<$assignmentsDiff; $i++){
                $scheduleAssignments->removeElement($scheduleAssignments->last());
            }
        }
        
        $loop_indx = 0;
        foreach($assignments as $assignment){ 
            if(!is_array($assignment)){
                throw new InvalidRequestException("Assignments must be array of assignment definitions");
            }           
            $scheduleAssignment =  ($modificationType === 'OVERWRITE' && $scheduleAssignments->containsKey($loop_indx)) ? 
            $scheduleAssignments[$loop_indx] : new ScheduleAssignment();
            $this->object->addAssignment($scheduleAssignment);
            $this->setterHelper->updateObjectSettings($scheduleAssignment, $assignment);
            $loop_indx++;
        }
    }

    

}