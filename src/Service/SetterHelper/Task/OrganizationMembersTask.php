<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @property Organization $object */
class OrganizationMembersTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    const MODIFICATION_TYPES = ['ADD', 'REMOVE', 'PATCH', 'OVERWRITE'];

    public function __construct(private SetterHelperInterface $setterHelper, private ValidatorInterface $validator)
    {
        
    }

    public function runPreValidation(array $members, string $modificationType)
    {
        if(!in_array($modificationType, self::MODIFICATION_TYPES)){
            throw new InvalidRequestException('Invalid modification type. Allowed modifications types: ADD, REMOVE, OVERWRITE');
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
                throw new InvalidRequestException("Parameter members must be array of integers");
            }

            $member = $this->object->getMembers()->findFirst(function($key, $element) use ($memberId){
                return $element->getId() === $memberId;
            });
            
            if(!$member){
                throw new InvalidRequestException("Member with id = {$memberId} does not exist");
            }
            
            $adminCount = $this->object->getAdmins()->count();
            if($member->hasRoles(['ADMIN']) && $adminCount === 1){
                throw new InvalidRequestException("Cannot remove member with id = {$memberId}, organization needs to have at least one admin");
            }

            $this->object->removeMember($member);
        }
    }

    private function addMembers(array $members):void
    {
        $loop_indx = 0;
        foreach($members as $settings){
            $member = new OrganizationMember();
            if(!is_array($settings)){
                throw new InvalidRequestException("Parameter members must be array of settings arrays");
            }

            $this->object->addMember($member);
            $this->setterHelper->updateObjectSettings($member, $settings, ['Default', 'user']);
            $this->validateMember($loop_indx, $member, $this->setterHelper->getValidationErrors()); 
            $loop_indx++;
        }
    }

    private function patchMembers(array $members):void
    {
        $loop_indx = 0;
        foreach($members as $settings){
            if(!is_array($settings)){
                throw new InvalidRequestException("Parameter members must be array of settings arrays");
            }

            if(!array_key_exists('id', $settings)){
                throw new InvalidRequestException('Parameter id is required');
            }

            $id = $settings['id'];
            $member = $this->object->getMembers()->findFirst(function($key,$element) use ($id){
                return $element->getId() == $id;
            });

            unset($settings['id']);

            if(!$member){
                throw new InvalidRequestException("Member with id = {$id} does not exist");
            }

            $this->setterHelper->updateObjectSettings($member, $settings, [], ['roles']);
            $this->validateMember($loop_indx, $member, $this->setterHelper->getValidationErrors()); 
            $loop_indx++;
        }
    }

    private function overwriteMembers(array $members):void
    {
        $organizationMembers = new ArrayCollection($this->object->getMembers()->getValues());
        $this->object->getMembers()->clear();
        
        $loop_indx = 0;
        foreach($members as $settings){
            if(!is_array($settings)){
                throw new InvalidRequestException("Parameter members must be array of settings arrays");
            }

            if(array_key_exists('id', $settings)){
                $id = $settings['id'];
                $member = $organizationMembers->findFirst(function($key,$element) use ($id){
                    return $element->getId() == $id;
                });
                if(!$member){
                    throw new InvalidRequestException("Member with id = {$id} does not exist");
                }
                unset($settings['id']);
            }
            else{
                $member = new OrganizationMember();
                
            }

            $this->object->addMember($member);
            $this->setterHelper->updateObjectSettings($member, $settings, ['Default', 'user']);
            $this->validateMember($loop_indx, $member, $this->setterHelper->getValidationErrors()); 
            $loop_indx++;
        }
        
        $adminCount = $this->object->getAdmins()->count();
        if($adminCount < 1)
        {
            throw new InvalidRequestException("Organization needs to have at least one admin");
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