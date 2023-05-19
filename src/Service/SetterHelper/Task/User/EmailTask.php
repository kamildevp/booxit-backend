<?php

namespace App\Service\SetterHelper\Task\User;

use App\Entity\User;
use App\Service\MailingHelper\MailingHelper;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\ORM\EntityManagerInterface;

/** @property User $object */
class EmailTask implements SetterTaskInterface
{
    use SetterTaskTrait;
    private ?string $oldEmail;

    public function __construct(private EntityManagerInterface $entityManager, private MailingHelper $mailingHelper)
    {

    }

    public function runPreValidation(string $email)
    {
        $this->validationGroups[] = 'Default';

        $this->oldEmail = $this->object->getEmail();
        $this->object->setEmail($email);
    }

    public function runPostValidation():void
    {
        if(is_null($this->oldEmail) || $this->object->getEmail() === $this->oldEmail){
                return;
        }

        $this->mailingHelper->newEmailVerification($this->object, $this->object->getEmail()); 
        $this->object->setEmail($this->oldEmail);
    }



}