<?php

namespace App\Service\Auth;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Exceptions\RefreshTokenCompromisedException;
use App\Exceptions\TokenRefreshFailedException;
use App\Exceptions\UnauthorizedException;
use App\Model\RefreshTokenPayload;
use App\Repository\RefreshTokenRepository;
use App\Service\Auth\AccessRule\AccessRuleInterface;
use App\Service\Auth\Attribute\RestrictedAccess;
use DateInterval;
use DateTime;
use InvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthService implements AuthServiceInterface
{
    const REFRESH_TOKEN_TTL = 259200;

    private ?UserInterface $user = null;

    public function __construct(
        private Security $security, 
        private RefreshTokenRepository $refreshTokenRepository, 
        private JWTTokenManagerInterface $jwtTokenManager
    )
    {
        $this->user = $this->security->getUser();
    }

    public function validateAccess(AbstractController $controller, string $methodName, Request $request): void
    {
        $this->validateLocationAccess($request, $controller);
        $this->validateLocationAccess($request, $controller, $methodName);
    }

    public function getAuthorizedUserOrFail(): User
    {
        if(!($this->user instanceof User)){
            throw new UnauthorizedException;
        }

        return $this->user;
    }

    private function validateLocationAccess(Request $request, AbstractController $controller, ?string $methodName = null): void
    {
        $restrictedAccessAttributes = $this->getRestrictedAccessAttributes($controller, $methodName);

        foreach($restrictedAccessAttributes as $attribute){
            $accessRule = $this->resolveAccessRule($attribute);
            $accessRule->validateAccess($this->user, $request);
        }
    }

    private function getRestrictedAccessAttributes(AbstractController $controller, ?string $methodName = null): array
    {
        $reflection = $methodName ? new ReflectionMethod($controller, $methodName) : new ReflectionClass($controller);
        return $reflection->getAttributes(RestrictedAccess::class);
    }

    /**
     * @param ReflectionAttribute<RestrictedAccess> $attribute
     */
    private function resolveAccessRule(ReflectionAttribute $attribute): AccessRuleInterface
    {
        $accessRuleClass = $attribute->newInstance()->accessRule;
        $accessRule = new $accessRuleClass;
        if(!($accessRule instanceof AccessRuleInterface)){
            throw new InvalidArgumentException('Access Rule must implement AccessRuleInterface');
        }

        return $accessRule;
    }

    public function createUserRefreshToken(User $user): string
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setAppUser($user);
        $this->refreshTokenRepository->save($refreshToken, true);

        $this->regenerateRefreshToken($refreshToken);
        $this->refreshTokenRepository->save($refreshToken, true);
        
        return $refreshToken->getValue();
    }

    public function refreshUserToken(string $refreshTokenValue): RefreshToken
    {
        try{
            $refreshToken = $this->resolveRefreshToken($refreshTokenValue);
            $this->regenerateRefreshToken($refreshToken);
            $this->refreshTokenRepository->save($refreshToken, true);
        }
        catch(RefreshTokenCompromisedException $e){
            $this->refreshTokenRepository->removeAllUserRefreshTokens($e->getUser());
            throw new TokenRefreshFailedException();
        }

        return $refreshToken;
    }

    private function generateRefreshTokenValue(User $user, int $refreshTokenId, DateTime $expiryDate): string
    {
        return $this->jwtTokenManager->createFromPayload($user, [
            'refresh_token_id' => $refreshTokenId,
            'exp' => $expiryDate->getTimestamp()
        ]);
    }

    private function generateRefreshTokenExpiryDate(int $ttl): DateTime
    {
        $interval = new DateInterval('PT' . $ttl . 'S');
        $expiryDate = new DateTime();
        $expiryDate->add($interval);
        
        return $expiryDate;
    }

    private function regenerateRefreshToken(RefreshToken $refreshToken): void
    {
        $expiryDate = $this->generateRefreshTokenExpiryDate(self::REFRESH_TOKEN_TTL);
        $refreshToken->setExpiresAt($expiryDate);
        $value = $this->generateRefreshTokenValue($refreshToken->getAppUser(), $refreshToken->getId(), $expiryDate);
        $refreshToken->setValue($value);
    }

    private function resolveRefreshToken(string $refreshTokenValue): RefreshToken
    {
        $tokenPayload = $this->parseRefreshToken($refreshTokenValue);
        $refreshToken = $this->refreshTokenRepository->find($tokenPayload->getRefreshTokenId());
        if($refreshToken == null){
            throw new TokenRefreshFailedException();
        }

        $refreshTokenUser = $refreshToken->getAppUser();
        if($refreshTokenUser->getId() != $tokenPayload->getUserId() || $refreshToken->getValue() != $refreshTokenValue){
            throw new RefreshTokenCompromisedException($refreshTokenUser);
        }

        return $refreshToken;
    }


    private function parseRefreshToken(string $refreshToken): RefreshTokenPayload
    {
        try{
            $tokenPayload = $this->jwtTokenManager->parse($refreshToken);
        }
        catch(JWTDecodeFailureException $e){
            throw new TokenRefreshFailedException();
        }
        
        if($tokenPayload === false || !isset($tokenPayload['id']) || !isset($tokenPayload['refresh_token_id'])){
            throw new TokenRefreshFailedException();
        }

        return new RefreshTokenPayload((int)$tokenPayload['id'], (int)$tokenPayload['refresh_token_id']);
    }
}