<?php

namespace App\Service\SetterHelper\Task\User;

use App\Entity\User;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** @property User $object */
class PasswordTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
        
    }

    public function runPreValidation(string $password, ?string $oldPassword = null):void
    {
        $this->validationGroups[] = 'plainPassword';
        
        if(!is_null($this->object->getPassword()) && is_null($oldPassword)){
            $this->requestErrors['oldPassword'] = "Parameter is required";
            return;
        }

        if(!is_null($this->object->getPassword()) && !$this->passwordHasher->isPasswordValid($this->object, $oldPassword)){
            $this->validationErrors['oldPassword'] = 'Old password is invalid';
            return;
        }

        $this->object->setPlainPassword($password);
    }

    public function runPostValidation():void
    {
        $password = $this->passwordHasher->hashPassword($this->object, $this->object->getPlainPassword());
        $this->object->setPassword($password);
    }



}