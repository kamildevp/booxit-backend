<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\DTO\EmailConfirmation\VerifyEmailConfirmationDTO;
use App\Entity\EmailConfirmation;
use App\Entity\User;
use App\Exceptions\VerifyEmailConfirmationException;
use App\Message\EmailVerification;
use App\Repository\EmailConfirmationRepository;
use App\Service\EmailConfirmation\EmailConfirmationHandlerInterface;
use DateTime;
use DateTimeInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EmailConfirmationService
{
    const DEFAULT_EMAIL_CONFIRMATION_EXPIRY = '+1 day';

    public function __construct(
        protected EmailConfirmationHandlerInterface $emailConfirmationHandler,
        protected MessageBusInterface $bus,
        protected EmailConfirmationRepository $emailConfirmationRepository
    )
    {

    }

    public function setupEmailConfirmation(
        User $user, 
        string $email, 
        string $verificationHandler, 
        string $type,
        bool $removeUserOnFail = false,
        ?DateTimeInterface $expiryDate = null 
    ){
        $expiryDate = $expiryDate ?? new DateTime(self::DEFAULT_EMAIL_CONFIRMATION_EXPIRY);

        $emailConfirmation = new EmailConfirmation();
        $emailConfirmation->setCreator($user);
        $emailConfirmation->setEmail($email);
        $emailConfirmation->setExpiryDate($expiryDate);
        $emailConfirmation->setVerificationHandler($verificationHandler);
        $emailConfirmation->setType($type);

        $this->emailConfirmationRepository->save($emailConfirmation, true);
        $this->bus->dispatch(new EmailVerification($emailConfirmation->getId(), $removeUserOnFail));
    }

    public function validateEmailConfirmation(VerifyEmailConfirmationDTO $dto): bool
    {
        try{
            $this->resolveEmailConfirmation($dto);
        }
        catch(VerifyEmailConfirmationException)
        {
            return false;
        }

        return true;
    }


    public function resolveEmailConfirmation(VerifyEmailConfirmationDTO $dto): EmailConfirmation
    {
        $emailConfirmation = $this->emailConfirmationRepository->find($dto->id);
        if(!$emailConfirmation){
            throw new VerifyEmailConfirmationException();
        }

        $valid = $this->emailConfirmationHandler->validateEmailConfirmation(
            $emailConfirmation,
            $dto->token,
            $dto->_hash,
            $dto->expires,
            $dto->type
        );
        if(!$valid){
            throw new VerifyEmailConfirmationException();
        }

        return $emailConfirmation;
    }
}