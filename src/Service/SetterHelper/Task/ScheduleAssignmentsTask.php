<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\Schedule;
use App\Entity\ScheduleAssignment;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\Common\Collections\ArrayCollection;

/** @property Schedule $object */
class ScheduleAssignmentsTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    const MODIFICATION_TYPES = ['ADD', 'REMOVE', 'PATCH', 'OVERWRITE'];

    public function __construct(private SetterHelperInterface $setterHelper)
    {
        
    }

    public function runPreValidation(array $assignments, string $modificationType)
    {
        if(!in_array($modificationType, self::MODIFICATION_TYPES)){
            throw new InvalidRequestException('Invalid modification type. Allowed modifications types: ADD, REMOVE, OVERWRITE');
        }

        switch($modificationType){
            case 'REMOVE':
                $this->removeAssignments($assignments);
                break;
            case 'ADD':
                $this->addAssignments($assignments);
                break;
            case 'PATCH':
                $this->patchAssignments($assignments);
                break;
            case 'OVERWRITE':
                $this->overwriteAssignments($assignments);
                break;
        }
        
    }

    private function removeAssignments(array $assignments):void
    {
        foreach($assignments as $assignmentId){
            if(!is_int($assignmentId)){
                throw new InvalidRequestException("Parameter assigments must be array of integers");
            }

            $assignment = $this->object->getAssignments()->findFirst(function($key, $element) use ($assignmentId){
                return $element->getId() === $assignmentId;
            });
            
            if(!$assignment){
                throw new InvalidRequestException("Assignment with id = {$assignmentId} does not exist");
            }

            $this->object->removeAssignment($assignment);
        }
    }

    private function addAssignments(array $assignments):void
    {
        foreach($assignments as $settings){
            $assignment = new ScheduleAssignment();
            if(!is_array($settings)){
                throw new InvalidRequestException("Parameter assignments must be array of settings arrays");
            }

            $this->object->addAssignment($assignment);
            $this->setterHelper->updateObjectSettings($assignment, $settings, ['Default']);
        }
    }

    private function patchAssignments(array $assignments):void
    {
        foreach($assignments as $settings)
        {
            if(!is_array($settings)){
                throw new InvalidRequestException("Parameter assignments must be array of settings arrays");
            }

            if(!array_key_exists('id', $settings)){
                throw new InvalidRequestException('Parameter id is required');
            }

            $id = $settings['id'];
            $assignment = $this->object->getAssignments()->findFirst(function($key, $element) use ($id){
                return $element->getId() === $id;
            });
            
            if(!$assignment){
                throw new InvalidRequestException("Assignment with id = {$id} does not exist");
            }

            unset($settings['id']);

            $this->setterHelper->updateObjectSettings($assignment, $settings);
        }
    }

    private function overwriteAssignments(array $assignments):void
    {
        $assignmentsCollection = new ArrayCollection($this->object->getAssignments()->getValues());
        $this->object->getAssignments()->clear();
        
        foreach($assignments as $settings){
            if(!is_array($settings)){
                throw new InvalidRequestException("Parameter assignments must be array of settings arrays");
            }

            if(array_key_exists('id', $settings)){
                $id = $settings['id'];
                $assignment = $assignmentsCollection->findFirst(function($key,$element) use ($id){
                    return $element->getId() == $id;
                });
                if(!$assignment){
                    throw new InvalidRequestException("Assignment with id = {$id} does not exist");
                }
                unset($settings['id']);
            }
            else{
                $assignment = new ScheduleAssignment();
            }

            $this->object->addAssignment($assignment);
            $this->setterHelper->updateObjectSettings($assignment, $settings, ['Default']);
        }
    }

}