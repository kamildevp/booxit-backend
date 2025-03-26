<?php

namespace App\Service\SetterHelper\Task\User;

use App\Entity\User;
use App\Message\EmailVerification;
use App\Service\MailingHelper\MailingHelper;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/** @property User $object */
class EmailTask implements SetterTaskInterface
{
    use SetterTaskTrait;
    private ?string $oldEmail;

    public function __construct(
        private EntityManagerInterface $entityManager, 
        private MailingHelper $mailingHelper,
        private MessageBusInterface $bus 
    )
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

        $this->bus->dispatch(new EmailVerification($this->object->getId(), true));
        $this->object->setEmail($this->oldEmail);
    }



}