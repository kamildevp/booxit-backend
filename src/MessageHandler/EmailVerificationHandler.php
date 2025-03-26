<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Exceptions\MailingHelperException;
use App\Message\EmailVerification;
use App\Repository\UserRepository;
use App\Service\MailingHelper\MailingHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EmailVerificationHandler
{
    protected UserRepository $userRepository;

    public function __construct(
        protected MailingHelper $mailingHelper,
        protected EntityManagerInterface $entityManager, 
    )
    {
        $this->userRepository = $this->entityManager->getRepository(User::class);
    }

    public function __invoke(EmailVerification $message)
    {
        $user = $this->userRepository->find($message->getUserId());
        if(!$user){
            return;
        }

        try{
            $this->mailingHelper->newEmailVerification($user, $user->getEmail());
        }
        catch(MailingHelperException){
            if($message->shouldUserBeRemovedOnFailure()){
                $this->userRepository->remove($user, true);
            }
        }
    }
}