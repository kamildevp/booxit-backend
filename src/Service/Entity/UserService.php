<?php

namespace App\Service\Entity;

use App\Entity\EmailConfirmation;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Enum\User\UserSetterGroup;
use App\Exceptions\MailingHelperException;
use App\Exceptions\VerifyEmailException;
use App\Repository\EmailConfirmationRepository;
use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use App\Service\MailingHelper\MailingHelper;
use App\Service\EntityHandler\EntityHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class UserService
{
    protected UserRepository $userRepository;
    protected EmailConfirmationRepository $emailConfirmationRepository;
    protected RefreshTokenRepository $refreshTokenRepository;

    public function __construct(
        protected EntityManagerInterface $entityManager, 
        protected EntityHandlerInterface $entityHandler,
        protected MailingHelper $mailingHelper,   
        protected VerifyEmailHelperInterface $verifyEmailHelper 
    )
    {
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->emailConfirmationRepository = $this->entityManager->getRepository(EmailConfirmation::class);
        $this->refreshTokenRepository = $this->entityManager->getRepository(RefreshToken::class);
    }

    public function createUser(array $params): User
    {
        $user = new User();
        $this->entityHandler->parseParamsToEntity($user, $params, [UserSetterGroup::ALL->value]);

        $user->setVerified(false);
        $user->setExpiryDate(new \DateTime('+1 days'));
        $this->userRepository->save($user, true);

        try{
            $this->mailingHelper->newEmailVerification($user, $user->getEmail());
        }
        catch(MailingHelperException $e){
            $this->userRepository->remove($user, true);
            throw $e;
        }

        return $user;
    }

    public function verifyUserEmail(int $emailConfirmationId, string $uri): void
    {
        $emailConfirmation = $this->emailConfirmationRepository->find($emailConfirmationId);
        if(!$emailConfirmation){
            throw new VerifyEmailException('Verification link is invalid');
        }

        try{
            $this->verifyEmailHelper->validateEmailConfirmation($uri, $emailConfirmation->getId(), $emailConfirmation->getEmail());
            $user = $emailConfirmation->getCreator();

            $user->setEmail($emailConfirmation->getEmail());
            $user->setVerified(true);
            $user->setExpiryDate(null);
            $this->emailConfirmationRepository->remove($emailConfirmation, true);
        } 
        catch(VerifyEmailExceptionInterface $e) {
            throw new VerifyEmailException($e->getReason());
        }
    }
}