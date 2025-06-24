<?php

namespace App\Service\Entity;

use App\DTO\EmailConfirmation\VerifyEmailConfirmationDTO;
use App\DTO\User\UserCreateDTO;
use App\DTO\User\UserPatchDTO;
use App\Entity\EmailConfirmation;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Enum\EmailConfirmationType;
use App\Exceptions\VerifyEmailConfirmationException;
use App\Repository\EmailConfirmationRepository;
use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use App\Service\EmailConfirmation\EmailConfirmationHandlerInterface;
use App\Service\MailingHelper\MailingHelper;
use App\Service\EntitySerializer\EntitySerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use App\DTO\User\UserResetPasswordDTO;
use App\DTO\User\UserResetPasswordRequestDTO;
use App\Exceptions\InvalidActionException;
use App\Service\Auth\AuthServiceInterface;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;

class UserService
{
    protected UserRepository $userRepository;
    protected EmailConfirmationRepository $emailConfirmationRepository;
    protected RefreshTokenRepository $refreshTokenRepository;

    public function __construct(
        protected EntityManagerInterface $entityManager, 
        protected EntitySerializerInterface $entitySerializer,
        protected EmailConfirmationService $emailConfirmationService,
        protected UserPasswordHasherInterface $passwordHasher    
    )
    {
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->emailConfirmationRepository = $this->entityManager->getRepository(EmailConfirmation::class);
        $this->refreshTokenRepository = $this->entityManager->getRepository(RefreshToken::class);
    }

    public function createUser(UserCreateDTO $dto): User
    {
        $user = $this->entitySerializer->parseToEntity($dto->toArray(['password']), User::class);
        $user->setPassword($this->passwordHasher->hashPassword($user, $dto->password));

        $user->setVerified(false);
        $user->setExpiryDate(new DateTime(EmailConfirmationService::DEFAULT_EMAIL_CONFIRMATION_EXPIRY));
        $this->userRepository->save($user, true);
        $this->emailConfirmationService->setupEmailConfirmation(
            $user, 
            $dto->email, 
            $dto->verificationHandler, 
            EmailConfirmationType::USER_VERIFICATION->value,
            true
        );

        return $user;
    }

    public function patchUser(User $user, UserPatchDTO $dto): User
    {
        $user = $this->entitySerializer->parseToEntity($dto->toArray(['email']), $user);

        $this->userRepository->save($user, true);

        if($user->getEmail() != $dto->email){
            $this->emailConfirmationService->setupEmailConfirmation(
                $user, 
                $dto->email, 
                $dto->verificationHandler, 
                EmailConfirmationType::EMAIL_VERIFICATION->value
            );
        }

        return $user;
    }

    public function changeUserPassword(User $user, string $password, bool $logoutOtherSessions, ?RefreshToken $refreshToken = null): void
    {
        if(
            !is_null($refreshToken) && 
            $refreshToken->getAppUser()->getId() != $user->getId()
        ){
            throw new InvalidActionException('User does not match provided refresh token');
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

    public function verifyUserEmail(VerifyEmailConfirmationDTO $dto): bool
    {
        try{
            $emailConfirmation = $this->emailConfirmationService->resolveEmailConfirmation($dto);
        }
        catch(VerifyEmailConfirmationException)
        {
            return false;
        }

        $user = $emailConfirmation->getCreator();
        $user->setEmail($emailConfirmation->getEmail());
        $user->setVerified(true);
        $user->setExpiryDate(null);
        $this->emailConfirmationRepository->remove($emailConfirmation, true);

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

            $this->emailConfirmationService->setupEmailConfirmation(
                $user, 
                $user->getEmail(), 
                $dto->verificationHandler,
                EmailConfirmationType::PASSWORD_RESET->value
            );
        }
    }

    public function resetUserPassword(UserResetPasswordDTO $dto): bool
    {
        try{
            $emailConfirmation = $this->emailConfirmationService->resolveEmailConfirmation($dto);
        }
        catch(VerifyEmailConfirmationException)
        {
            return false;
        }

        $user = $emailConfirmation->getCreator();
        $this->changeUserPassword($user, $dto->password, true);
        $this->emailConfirmationRepository->remove($emailConfirmation, true);

        return true;
    }
}