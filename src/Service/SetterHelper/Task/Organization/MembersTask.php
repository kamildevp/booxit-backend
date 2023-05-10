<?php

namespace App\Service\SetterHelper\Task\Organization;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @property Organization $object */
class MembersTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    const MODIFICATION_TYPES = ['ADD', 'REMOVE', 'PATCH', 'OVERWRITE'];

    public function __construct(private SetterHelperInterface $setterHelper, private ValidatorInterface $validator)
    {
        
    }

    public function runPreValidation(array $members, string $modificationType)
    {
        if(!in_array($modificationType, self::MODIFICATION_TYPES)){
            $modificationTypesString = join(', ', self::MODIFICATION_TYPES);
            $this->validationErrors['modificationType'] = "Invalid modification type. Allowed modifications types: {$modificationTypesString}";
            return;
        }

        switch($modificationType){
            case 'REMOVE':
                $this->removeMembers($members);
                break;
            case 'ADD':
                $this->addMembers($members);
                break;
            case 'PATCH':
                $this->patchMembers($members);
                break;
            case 'OVERWRITE':
                $this->overwriteMembers($members);
                break;
        }
        
    }

    private function removeMembers(array $members):void
    {
        foreach($members as $memberId){
            if(!is_int($memberId)){
                $this->requestErrors['members'] = "Parameter must be array of integers";
                return;
            }

            $member = $this->object->getMembers()->findFirst(function($key, $element) use ($memberId){
                return $element->getId() === $memberId;
            });
            
            if(!$member){
                $this->requestErrors['members'][] = "Member with id = {$memberId} does not exist";
                continue;
            }
            
            $adminCount = $this->object->getAdmins()->count();
            if($member->hasRoles(['ADMIN']) && $adminCount === 1){
                $this->requestErrors['members'][] = "Cannot remove member with id = {$memberId}, organization needs to have at least one admin";
                continue;
            }

            $this->object->removeMember($member);
        }
    }

    private function addMembers(array $members):void
    {
        $loopIndx = 0;
        foreach($members as $settings){
            $member = new OrganizationMember();
            if(!is_array($settings)){
                $this->requestErrors['members'][$loopIndx] = "Parameter must be array of member settings";
                $loopIndx++;
                continue;
            }

            $this->object->addMember($member);
            try{
                $this->setterHelper->updateObjectSettings($member, $settings, ['Default', 'user']);
            }
            catch(InvalidRequestException){
                $this->requestErrors['members'][$loopIndx] = $this->setterHelper->getRequestErrors();
                $loopIndx++;
                continue;
            }
            $this->validateMember($loopIndx, $member, $this->setterHelper->getValidationErrors()); 
            $loopIndx++;
        }
    }

    private function patchMembers(array $members):void
    {
        $loopIndx = 0;
        foreach($members as $settings){
            if(!is_array($settings)){
                $this->requestErrors['members'][$loopIndx] = "Parameter must be array of member settings";
                $loopIndx++;
                continue;
            }

            if(!array_key_exists('id', $settings)){
                $this->requestErrors['members'][$loopIndx]['id'] = 'Parameter is required';
                $loopIndx++;
                continue;
            }

            $id = $settings['id'];
            $member = $this->object->getMembers()->findFirst(function($key,$element) use ($id){
                return $element->getId() == $id;
            });

            unset($settings['id']);

            if(!$member){
                $this->requestErrors['members'][$loopIndx]['id'] = "Member with id = {$id} does not exist";
                $loopIndx++;
                continue;
            }

            try{
                $this->setterHelper->updateObjectSettings($member, $settings, [], ['roles']);
            }
            catch(InvalidRequestException){
                $this->requestErrors['members'][$loopIndx] = $this->setterHelper->getRequestErrors();
                $loopIndx++;
                continue;
            }
            
            $this->validateMember($loopIndx, $member, $this->setterHelper->getValidationErrors()); 
            $loopIndx++;
        }
    }

    private function overwriteMembers(array $members):void
    {
        $organizationMembers = new ArrayCollection($this->object->getMembers()->getValues());
        $this->object->getMembers()->clear();
        
        $loopIndx = 0;
        foreach($members as $settings){
            if(!is_array($settings)){
                $this->requestErrors['members'][$loopIndx] = "Parameter must be array of member settings";
                $loopIndx++;
                continue;
            }

            if(!array_key_exists('id', $settings)){
                $this->requestErrors['members'][$loopIndx]['id'] = 'Parameter is required';
                $loopIndx++;
                continue;
            }

            $id = $settings['id'];
            if(!is_null($id)){
                $member = $organizationMembers->findFirst(function($key,$element) use ($id){
                    return $element->getId() == $id;
                });
                if(!$member){
                    $this->requestErrors['members'][$loopIndx]['id'] = "Member with id = {$id} does not exist";
                    $loopIndx++;
                    continue;
                }
                
            }
            else{
                $member = new OrganizationMember();
            }

            unset($settings['id']);

            $this->object->addMember($member);

            try{
                $this->setterHelper->updateObjectSettings($member, $settings, ['Default', 'user']);
            }
            catch(InvalidRequestException){
                $this->requestErrors['members'][$loopIndx] = $this->setterHelper->getRequestErrors();
                $loopIndx++;
                continue;
            }
            
            $this->validateMember($loopIndx, $member, $this->setterHelper->getValidationErrors()); 
            $loopIndx++;
        }

        if(!empty($this->requestErrors) || !empty($this->validationErrors)){
            return;
        }
        
        $adminCount = $this->object->getAdmins()->count();
        if($adminCount < 1)
        {
            $this->validationErrors['members'] = "Organization needs to have at least one admin";
        }
            
        
    }


    private function validateMember(string $id, OrganizationMember $member, array $validationErrors){
        foreach ($validationErrors as $parameterName => $error) {;
            $this->validationErrors['members'][$id][$parameterName] = $error;
        }
        
        $violations = $this->validator->validate($member);
        
        foreach ($violations as $violation) {
            $parameterAlias = $this->getParameterAlias($violation->getPropertyPath());
            $this->validationErrors['members'][$id][$parameterAlias] = $violation->getMessage();
        }
    }

}