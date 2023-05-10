<?php

namespace App\Service\SetterHelper\Task\Schedule;

use App\Entity\Schedule;
use App\Entity\ScheduleAssignment;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Task\SetterTaskInterface;
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
            $modificationTypesString = join(', ', self::MODIFICATION_TYPES);
            $this->validationErrors['modificationType'] = "Invalid modification type. Allowed modifications types: {$modificationTypesString}";
            return;
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
        foreach($assignments as $assignmentId)
        {
            if(!is_int($assignmentId)){
                $this->requestErrors['assignments'] = "Parameter must be array of integers";
                return;
            }

            $assignment = $this->object->getAssignments()->findFirst(function($key, $element) use ($assignmentId){
                return $element->getId() === $assignmentId;
            });
            
            if(!$assignment){
                $this->requestErrors['assignments'][] = "Assignment with id = {$assignmentId} does not exist";
                continue;
            }

            $this->object->removeAssignment($assignment);
        }
    }

    private function addAssignments(array $assignments):void
    {
        $loopIndx = 0;
        foreach($assignments as $settings)
        {
            $assignment = new ScheduleAssignment();
            if(!is_array($settings)){
                $this->requestErrors['assignments'][$loopIndx] = "Parameter must be array of assignment settings";
                $loopIndx++;
                continue;
            }

            $this->object->addAssignment($assignment);
            try{
                $this->setterHelper->updateObjectSettings($assignment, $settings, ['Default']);
            }
            catch(InvalidRequestException){
                $this->requestErrors['assignments'][$loopIndx] = $this->setterHelper->getRequestErrors();
            }

            $validationErrors = $this->setterHelper->getValidationErrors();
            if(!empty($validationErrors)){
                $this->validationErrors['assignments'][$loopIndx] = $this->setterHelper->getValidationErrors();
            }
            $loopIndx++;
        }
    }

    private function patchAssignments(array $assignments):void
    {
        $loopIndx = 0;
        foreach($assignments as $settings)
        {
            if(!is_array($settings)){
                $this->requestErrors['assignments'][$loopIndx] = "Parameter must be array of assignment settings";
                $loopIndx++;
                continue;
            }

            if(!array_key_exists('id', $settings)){
                $this->requestErrors['assignments'][$loopIndx]['id'] = 'Parameter id is required';
                $loopIndx++;
                continue;
            }

            $id = $settings['id'];
            $assignment = $this->object->getAssignments()->findFirst(function($key, $element) use ($id){
                return $element->getId() === $id;
            });
            
            if(!$assignment){
                $this->requestErrors['assignments'][$loopIndx]['id'] = "Assignment with id = {$id} does not exist";
                $loopIndx++;
                continue;
            }

            unset($settings['id']);

            try{
                $this->setterHelper->updateObjectSettings($assignment, $settings);
            }
            catch(InvalidRequestException){
                $this->requestErrors['assignments'][$loopIndx] = $this->setterHelper->getRequestErrors();
            }
            
            $validationErrors = $this->setterHelper->getValidationErrors();
            if(!empty($validationErrors)){
                $this->validationErrors['assignments'][$loopIndx] = $this->setterHelper->getValidationErrors();
            }
            $loopIndx++;
        }
    }

    private function overwriteAssignments(array $assignments):void
    {
        $assignmentsCollection = new ArrayCollection($this->object->getAssignments()->getValues());
        $this->object->getAssignments()->clear();
        
        $loopIndx = 0;
        foreach($assignments as $settings){
            if(!is_array($settings)){
                $this->requestErrors['assignments'][$loopIndx] = "Parameter must be array of assignment settings";
                $loopIndx++;
                continue;
            }

            if(!array_key_exists('id', $settings)){
                $this->requestErrors['assignments'][$loopIndx]['id'] = 'Parameter id is required';
                $loopIndx++;
                continue;
            }

            $id = $settings['id'];
            if(!is_null($id)){
                $assignment = $assignmentsCollection->findFirst(function($key,$element) use ($id){
                    return $element->getId() == $id;
                });
                if(!$assignment){
                    $this->requestErrors['assignments'][$loopIndx]['id'] = "Assignment with id = {$id} does not exist";
                    $loopIndx++;
                    continue;
                }
            }
            else{
                $assignment = new ScheduleAssignment();
            }

            unset($settings['id']);

            $this->object->addAssignment($assignment);

            try{
                $this->setterHelper->updateObjectSettings($assignment, $settings, ['Default']);
            }
            catch(InvalidRequestException){
                $this->requestErrors['assignments'][$loopIndx] = $this->setterHelper->getRequestErrors();
            }

            $validationErrors = $this->setterHelper->getValidationErrors();
            if(!empty($validationErrors)){
                $this->validationErrors['assignments'][$loopIndx] = $this->setterHelper->getValidationErrors();
            }
            $loopIndx++;
        }
    }

}