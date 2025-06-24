<?php

namespace App\MessageHandler;

use App\Entity\EmailConfirmation;
use App\Entity\User;
use App\Exceptions\MailingHelperException;
use App\Message\EmailVerification;
use App\Repository\EmailConfirmationRepository;
use App\Service\EmailConfirmation\Exception\ResolveVerificationHandlerException;
use App\Service\MailingHelper\MailingHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EmailVerificationHandler
{
    protected EmailConfirmationRepository $emailConfirmationRepository;

    public function __construct(
        protected MailingHelper $mailingHelper,
        protected EntityManagerInterface $entityManager, 
    )
    {
        $this->emailConfirmationRepository = $this->entityManager->getRepository(EmailConfirmation::class);
    }

    public function __invoke(EmailVerification $message)
    {
        $emailConfirmation = $this->emailConfirmationRepository->find($message->getEmailConfirmationId());
        if(!$emailConfirmation){
            return;
        }

        try{
            $this->mailingHelper->sendEmailVerification($emailConfirmation);
        }
        catch(MailingHelperException | ResolveVerificationHandlerException){
            if($message->shouldUserBeRemovedOnFailure()){
                $user = $emailConfirmation->getCreator();
                $userRepository = $this->entityManager->getRepository(User::class);
                $userRepository->remove($user, true);
            }
        }
    }
}