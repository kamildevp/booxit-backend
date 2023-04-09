<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\User;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** @property User $object */
class PasswordTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
        $this->validationGroups[] = 'plainPassword';
    }

    public function runPreValidation(string $password, string $oldPassword):void
    {
        if(!$this->passwordHasher->isPasswordValid($this->object, $oldPassword)){
            throw new InvalidRequestException('Old password is invalid');
        }

        $this->object->setPlainPassword($password);
    }

    public function runPostValidation():void
    {
        $password = $this->passwordHasher->hashPassword($this->object, $this->object->getPlainPassword());
        $this->object->setPassword($password);
    }



}