<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\User;
use App\Service\MailingHelper\MailingHelper;
use App\Service\SetterHelper\Trait\SetterTaskTrait;

/** @property User $object */
class EmailTask implements SetterTaskInterface
{
    use SetterTaskTrait;
    private string $oldEmail;

    public function __construct(private MailingHelper $mailingHelper)
    {

    }

    public function runPreValidation(string $email)
    {
        $this->oldEmail = $this->object->getEmail();
        $this->object->setEmail($email);
    }

    public function runPostValidation():void
    {
        if($this->object->getEmail() === $this->oldEmail){
                return;
        }

        // $this->mailingHelper->newEmailVerification($this->object, $this->object->getEmail()); //uncomment when mailing provider is available
        // $this->object->setEmail($this->oldEmail); //uncomment when mailing provider is available
    }



}