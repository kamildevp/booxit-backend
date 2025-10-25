<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\DTO\User\UserCreateDTO;
use App\DTO\User\UserPatchDTO;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Enum\EmailConfirmation\EmailConfirmationType;
use App\Exceptions\VerifyEmailConfirmationException;
use App\Repository\EmailConfirmationRepository;
use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use App\Service\EntitySerializer\EntitySerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\DTO\User\UserResetPasswordDTO;
use App\DTO\User\UserResetPasswordRequestDTO;
use App\DTO\User\UserVerifyEmailDTO;
use App\Enum\EmailConfirmation\EmailConfirmationStatus;
use App\Enum\EmailType;
use App\Exceptions\ConflictException;
use App\Message\AccountActivationMessage;
use App\Message\EmailConfirmationMessage;
use App\Repository\OrganizationMemberRepository;
use App\Service\EmailConfirmation\EmailConfirmationHandlerInterface;
use DateTime;
use InvalidArgumentException;
use Symfony\Component\Messenger\MessageBusInterface;

class UserService
{
    public function __construct(
        protected EntitySerializerInterface $entitySerializer,
        protected EmailConfirmationService $emailConfirmationService,
        protected UserPasswordHasherInterface $passwordHasher,
        protected UserRepository $userRepository,
        protected EmailConfirmationRepository $emailConfirmationRepository,
        protected RefreshTokenRepository $refreshTokenRepository,   
        protected OrganizationMemberRepository $organizationMemberRepository,
        protected MessageBusInterface $bus,
        protected EmailConfirmationHandlerInterface $emailConfirmationHandler,
    )
    {

    }

    public function createUser(UserCreateDTO $dto): User
    {
        $user = $this->entitySerializer->parseToEntity($dto->toArray(['password']), User::class);
        $expiryDate = new DateTime(EmailConfirmationService::DEFAULT_EMAIL_CONFIRMATION_EXPIRY);

        $user->setPassword($this->passwordHasher->hashPassword($user, $dto->password));
        $user->setVerified(false);
        $user->setExpiryDate($expiryDate);
        $this->userRepository->save($user, true);

        $emailConfirmation = $this->emailConfirmationService->createEmailConfirmation( 
            $dto->email, 
            $dto->verificationHandler, 
            EmailConfirmationType::ACCOUNT_ACTIVATION->value,
            $user,
            $expiryDate,
        );

        $this->bus->dispatch(new AccountActivationMessage(
            $emailConfirmation->getId(),
            $user->getId(),
            EmailType::ACCOUNT_ACTIVATION->value,
            $dto->email,
            [
                'expiration_date' => $emailConfirmation->getExpiryDate(),
                'url' => $this->emailConfirmationHandler->generateSignedUrl($emailConfirmation)
            ],
            $user->getLanguagePreference()
        ));

        return $user;
    }

    public function patchUser(User $user, UserPatchDTO $dto): User
    {
        $user = $this->entitySerializer->parseToEntity($dto->toArray(['email']), $user);

        if($user->getEmail() != $dto->email){
            $emailConfirmation = $this->emailConfirmationService->createEmailConfirmation( 
                $dto->email, 
                $dto->verificationHandler, 
                EmailConfirmationType::EMAIL_VERIFICATION->value,
                $user,
            );

            $this->bus->dispatch(new EmailConfirmationMessage(
                $emailConfirmation->getId(),
                EmailType::EMAIL_VERIFICATION->value,
                $dto->email,
                [
                    'expiration_date' => $emailConfirmation->getExpiryDate(),
                    'url' => $this->emailConfirmationHandler->generateSignedUrl($emailConfirmation)
                ],
                $user->getLanguagePreference()
            ));
        }

        $this->userRepository->save($user, true);
        return $user;
    }

    public function changeUserPassword(User $user, string $password, bool $logoutOtherSessions, ?RefreshToken $refreshToken = null): void
    {
        if(
            !is_null($refreshToken) && 
            $refreshToken->getAppUser()->getId() != $user->getId()
        ){
            throw new InvalidArgumentException('User does not match provided refresh token');
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->userRepository->save($user, true);

        if($logoutOtherSessions && !is_null($refreshToken)){
            $this->refreshTokenRepository->removeAllUserRefreshTokensExceptIds($user, [$refreshToken->getId()]);
        }
        elseif($logoutOtherSessions){
            $this->refreshTokenRepository->removeAllUserRefreshTokens($user);
        }
    }

    public function verifyUserEmail(UserVerifyEmailDTO $dto): bool
    {
        try{
            $emailConfirmation = $this->emailConfirmationService->resolveEmailConfirmation(
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

        $user = $emailConfirmation->getCreator();
        $user->setEmail($emailConfirmation->getEmail());
        $user->setVerified(true);
        $user->setExpiryDate(null);
        $emailConfirmation->setStatus(EmailConfirmationStatus::COMPLETED->value);
        $this->emailConfirmationRepository->save($emailConfirmation, true);

        return true;
    }

    public function handleResetUserPasswordRequest(UserResetPasswordRequestDTO $dto): void
    {
        $user = $this->userRepository->findOneBy(['email' => $dto->email]);
        if($user){
            $activeEmailConfirmation = $this->emailConfirmationRepository->findActiveUserEmailConfirmationByType($user, EmailConfirmationType::PASSWORD_RESET->value);
            if($activeEmailConfirmation){
                return;
            }

            $emailConfirmation = $this->emailConfirmationService->createEmailConfirmation( 
                $dto->email, 
                $dto->verificationHandler, 
                EmailConfirmationType::PASSWORD_RESET->value,
                $user,
            );

            $this->bus->dispatch(new EmailConfirmationMessage(
                $emailConfirmation->getId(),
                EmailType::PASSWORD_RESET->value,
                $dto->email,
                [
                    'expiration_date' => $emailConfirmation->getExpiryDate(),
                    'url' => $this->emailConfirmationHandler->generateSignedUrl($emailConfirmation)
                ],
                $user->getLanguagePreference()
            ));
        }
    }

    public function resetUserPassword(UserResetPasswordDTO $dto): bool
    {
        try{
            $emailConfirmation = $this->emailConfirmationService->resolveEmailConfirmation(
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

        $user = $emailConfirmation->getCreator();
        $this->changeUserPassword($user, $dto->password, true);
        $emailConfirmation->setStatus(EmailConfirmationStatus::COMPLETED->value);
        $this->emailConfirmationRepository->save($emailConfirmation, true);

        return true;
    }

    public function removeUser(User $user): void
    {
        $orphanedOrganizationsCount = $this->organizationMemberRepository->countOrganizationsWhereUserIsTheOnlyAdmin($user);
        if($orphanedOrganizationsCount > 0){
                throw new ConflictException('This user cannot be removed because they are the sole administrator of one or more organizations. Please remove those organizations first.');
        }
        $this->userRepository->remove($user, true);
    }
}