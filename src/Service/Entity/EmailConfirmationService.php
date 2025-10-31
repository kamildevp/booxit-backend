<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\DTO\EmailConfirmation\ValidateEmailConfirmationDTO;
use App\Entity\EmailConfirmation;
use App\Entity\User;
use App\Enum\EmailConfirmation\EmailConfirmationStatus;
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

    public function createEmailConfirmation( 
        string $email, 
        string $verificationHandler, 
        string $type,
        ?User $creator = null,
        ?DateTimeInterface $expiryDate = null, 
        array $params = [],
    ){
        $emailConfirmation = new EmailConfirmation();
        $emailConfirmation->setEmail($email);
        $emailConfirmation->setExpiryDate($expiryDate ?? new DateTime(self::DEFAULT_EMAIL_CONFIRMATION_EXPIRY));
        $emailConfirmation->setVerificationHandler($verificationHandler);
        $emailConfirmation->setType($type);
        $emailConfirmation->setCreator($creator);
        $emailConfirmation->setParams($params);
        $emailConfirmation->setStatus(EmailConfirmationStatus::PENDING->value);

        $this->emailConfirmationRepository->save($emailConfirmation, true);
        return $emailConfirmation;
    }

    public function validateEmailConfirmation(ValidateEmailConfirmationDTO $dto): bool
    {
        try{
            $this->resolveEmailConfirmation(
                $dto->id,
                $dto->token,
                $dto->_hash,
                $dto->expires,
                $dto->type
            );
        }
        catch(VerifyEmailConfirmationException)
        {
            return false;
        }

        return true;
    }


    public function resolveEmailConfirmation(int $id, string $token, string $signature, int $expires, string $type): EmailConfirmation
    {
        $emailConfirmation = $this->emailConfirmationRepository->findOneBy(['id' => $id, 'status' => EmailConfirmationStatus::PENDING->value]);
        if(!$emailConfirmation){
            throw new VerifyEmailConfirmationException();
        }

        $valid = $this->emailConfirmationHandler->validateEmailConfirmation(
            $emailConfirmation,
            $token,
            $signature,
            $expires,
            $type
        );
        if(!$valid){
            throw new VerifyEmailConfirmationException();
        }

        return $emailConfirmation;
    }
}